# План: автоматическая конвертация PNG → WebP (репозиторий + админка WordPress)

Дата: 2026-07-21. Ветка: `wordpress` (worktree `.worktrees/wordpress`).

## 1. Что есть сейчас

| Область | Файлы | Состояние |
|---|---|---|
| Верстка `source/img` → `build/img` | 86 PNG | Конвертируется тасками gulp (`webpImages`, `gulp-webp`), watch на `{jpg,jpeg,png}` |
| Тема `wordpress/wp-content/themes/logika-theme/assets/img` | 125 PNG | **Не конвертируется автоматически** — webp-файлы кладутся руками, шаблоны уже ждут `<picture><source type="image/webp">` (напр. `template-parts/sections/cta.php`) |
| Медиабиблиотека `wp-content/uploads` | ~946 PNG (в git игнорируется, живёт на сервере, симлинк `DEPLOY_ROOT/uploads`) | **Никакой конвертации нет.** Контент-менеджер грузит PNG — он и отдаётся |
| Шаблоны | `src/PageContent.php:158` | Хак: подменяет `.png` на `.webp` по имени файла — держится на честном слове |

Итого ~390 МБ PNG. Основной вес — uploads.

## 2. Цели

1. Любой PNG в репозитории (верстка + ассеты темы) имеет актуального `.webp`-соседа; CI не пропускает PR без него.
2. Любой PNG/JPEG, загруженный через админку (Медиабиблиотека, ACF-поля, drag&drop в редакторе), автоматически получает WebP-версии оригинала и всех размеров.
3. Фронт отдаёт WebP с прозрачным фолбэком на PNG (без правки контента вручную).
4. Есть batch-инструмент, чтобы разово конвертировать 946 существующих PNG в uploads на сервере.

## 3. Часть A — репозиторий (сборка/CI)

### A1. Скрипт `scripts/media/convert-png-to-webp.mjs`
- Node + `sharp` (dev-зависимость; `gulp-webp`/`imagemin-webp` тянут устаревший `cwebp`-враппер, `sharp` быстрее и уже собран под CI).
- Вход: список каталогов из конфига `scripts/media/webp-targets.json`:
  - `source/img/**`
  - `wordpress/wp-content/themes/logika-theme/assets/img/**`
  - `wordpress/wp-content/plugins/logika-core/assets/**` (если появятся растры)
- Правила:
  - PNG с альфа-каналом → `webp` lossless или `quality: 90, alphaQuality: 100`; PNG без альфы и JPEG → `quality: 82`.
  - Идемпотентность: пропускать, если `.webp` существует и `mtime` ≥ mtime исходника (в CI сравнение по хэшу источника, хранится в `scripts/media/.webp-manifest.json`).
  - Флаги: `--check` (ничего не пишет, ненулевой код возврата + список отсутствующих/устаревших webp), `--force`, `--dir=`.
- npm-скрипты: `"media:webp": "node scripts/media/convert-png-to-webp.mjs"`, `"media:webp:check": "... --check"`.

### A2. Интеграция в gulp
- `webpImages` заменить/дополнить вызовом того же скрипта, чтобы правило качества было одно на проект; watch оставить.
- Добавить конвертацию ассетов темы в `exports.backend` и `exports.build` (сейчас тема вне gulp-пайплайна).

### A3. Git-хук (локально)
- `.githooks/pre-commit`: если в индексе есть `*.png` из целевых каталогов — прогнать `npm run media:webp` и `git add` полученных `.webp`. Подключение через `git config core.hooksPath .githooks` + строка в README/AGENTS.md.

### A4. CI
- В `.github/workflows/validate.yml` (запускается и на PR, и через `workflow_call` из `deploy-staging.yml`) добавить шаг `npm run media:webp:check` сразу после `npm ci`. PR с «голым» PNG падает с понятным сообщением.
- Осознанно **не** делаем авто-коммит в CI (ветка `wordpress` защищена деплоем — сборка артефакта должна быть детерминированной от коммита).

## 4. Часть B — WordPress runtime (админка)

Новый модуль плагина `logika-core`: `src/WebpUploads.php`, класс `Logika\Core\WebpUploads`, подключение в `logika-core.php` (`require_once` + `WebpUploads::register()`).

### B1. Генерация при загрузке
- Хук `wp_generate_attachment_metadata` (priority 20): после того как WP создал все размеры, для оригинала и каждого `sizes[*]` файла типа `image/png|image/jpeg` создаём `<file>.webp` рядом.
- Реализация через `wp_get_image_editor()` (использует Imagick или GD — оба поддерживают WebP в PHP 8.3), с параметрами качества из п. A1; для PNG с альфой — lossless.
- Пропускать: файлы > настраиваемого лимита (по умолчанию 8 МБ), уже существующие webp, GIF/SVG.
- Записывать список созданных файлов в `_logika_webp` post meta (нужно для удаления и отчётов).
- Ошибки — в `error_log` + admin notice «WebP недоступен: нет поддержки в GD/Imagick», без падения загрузки.

### B2. Альтернатива/дополнение — `image_editor_output_format`
- Фильтр `image_editor_output_format` заставляет WP сохранять **под-размеры** сразу в WebP. Это дешевле, но: оригинал остаётся PNG, а часть ACF-полей/шаблонов ссылается на `full`. Решение: включаем фильтр для под-размеров **и** генерим webp для оригинала по B1 → в итоге все варианты имеют webp.
- Флаг в настройках, чтобы можно было откатить одним переключателем.

### B3. Отдача на фронте
- Хелпер `Logika\Core\WebpUploads::picture( int $attachment_id, string $size, array $attrs )` → `<picture><source type="image/webp" srcset="…"><img …></picture>`.
- Фильтр `wp_calculate_image_srcset` / `wp_get_attachment_image_attributes`: не подменять src молча (риск для почтовых шаблонов и OG-тегов), а именно рендерить `<picture>` в местах вывода.
- Фильтр `the_content` для картинок из редактора: оборачивать `<img>` в `<picture>` только если webp-сосед реально существует.
- Заменить хак `PageContent.php:158` на вызов хелпера.
- Опционально (шаг 2, вне MVP): nginx `try_files` по `$http_accept` — не трогаем, т.к. конфиг хостинга вне репозитория.

### B4. Удаление
- Хук `delete_attachment`: удалить все файлы из `_logika_webp`.
- Хук `wp_delete_file`: подчистить webp при regenerate-thumbnails.

### B5. Backfill существующих 946 PNG
- WP-CLI команда `wp logika webp backfill [--batch=50] [--dry-run] [--force]` (регистрируется при `defined('WP_CLI')`).
- Идемпотентна, печатает прогресс и итоговую экономию в МБ.
- Плюс кнопка в `AdminUi` (страница «Logika → Медиа») с AJAX-батчами по 20 вложений — для контент-менеджера без SSH.
- Запуск на staging → проверка → на проде (`WP_CLI_BIN` уже прокинут в `scripts/release/deploy.sh`).

### B6. Настройки
Страница опций (`OptionsPage`) или константы в `wp-config`: `LOGIKA_WEBP_QUALITY`, `LOGIKA_WEBP_ENABLED`, лимит размера. Дефолты — в коде, чтобы работало без настройки.

## 5. Тесты

- `tests/webp-uploads.php` (по образцу существующих интеграционных тестов, гоняются через `scripts/release/run-wordpress-tests.sh` в DDEV):
  - загрузка PNG → создаются webp для оригинала и всех размеров;
  - PNG с прозрачностью → webp сохраняет альфу;
  - удаление вложения → webp-файлы удалены;
  - хелпер `picture()` даёт корректный `<picture>` и не ломается, если webp нет;
  - backfill идемпотентен.
- `tests/media-webp-script.test.mjs` (`node --test`, как `tests/release-infrastructure.test.mjs`): `--check` падает на PNG без webp, проходит после конвертации.
- Добавить оба в `validate.yml` (второй — в существующий шаг «Release tooling tests»).

## 6. Риски и решения

| Риск | Решение |
|---|---|
| Нет WebP в GD/Imagick на хостинге | Проверка при активации + admin notice; загрузка не ломается |
| Рост места в uploads (PNG + WebP) | Оригиналы нужны как фолбэк; webp ~25–35% от PNG. Отдельным шагом можно чистить неиспользуемые размеры |
| WebP тяжелее PNG (мелкие плоские картинки) | Скрипт/модуль не сохраняет webp, если он больше исходника |
| `uploads` в .gitignore | Backfill выполняется на сервере через WP-CLI, не через CI |
| Потеря прозрачности | lossless/alphaQuality 100 для PNG с альфой + тест |
| Сторонние плагины (EWWW, Imagify) | Не берём: конфликт с деплоем immutable-артефактом и лицензии; своя реализация ~250 строк |

## 7. Порядок работ

1. A1 + A2 + A4 (скрипт, gulp, CI-check) — самостоятельный, мержится первым.
2. Прогон `npm run media:webp` → коммит недостающих webp для 125 ассетов темы.
3. B1 + B4 + B6 (генерация и удаление) + тесты.
4. B3 (`<picture>`-хелпер, замена хака в `PageContent.php`).
5. B5 (WP-CLI backfill + кнопка в админке), прогон на staging, затем на проде.
6. A3 (git-хук) и документация в `AGENTS.md` / `README.md`.
