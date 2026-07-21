# Швидке створення курсів і таборів контент-менеджером

Дата: 2026-07-21. Гілка: `wordpress` (worktree `.worktrees/wordpress`).

## Статус

Реалізовано (мінімальний корисний зріз, етапи 1, 3, 4 + частина 6):
- `src/PostDuplicator.php` — дублювання курсу/табора в чернетку (row action «Дублювати», meta box у редакторі);
- `src/CourseCatalogSync.php` — автосинк `it_courses_age_categories` / `english_courses_featured_courses` за напрямом і віком, поле-запобіжник `course_show_in_catalog`;
- `src/CampArchiveSync.php` — автосинк `camp_archive_formats` за `camp_is_active`;
- поле `logika_is_template` додано в `course` і `camp` (позначка «показувати першим» — сам екран вибору шаблону з етапу 2 ще не зроблено, поле лише готує ґрунт);
- квік-лінк в `AdminUi::prepareCourseField()` тепер веде на список курсів (де є «Дублювати»), а не на порожній `post-new.php`;
- тести: `tests/post-duplicator.php`, `tests/course-catalog-sync.php`, `tests/camp-archive.php` (переписаний під реальну архітектуру), `tests/course-camp-architecture.php` (актуалізований).

Не зроблено: етап 2 (окремий екран «Створити з шаблону» з табами Програмування/Англійська — зараз лише row action + meta box), етап 5 (чек-лист готовності й `acf/validate_save_post` для публікації).

## Мета

Контент-менеджер має вміти за кілька хвилин: обрати існуючий курс/табір як шаблон → отримати чернетку з усією структурою → замінити тексти й картинки → натиснути «Опублікувати» → побачити картку в каталозі без жодних додаткових дій в інших екранах.

Сценарії: курс програмування, курс англійської, табір.

## Що вже є (перевірено в коді)

- CPT `course`, `camp` + таксономії `course_direction`, `age_group`, `learning_format` — `plugins/logika-core/src/ContentTypes.php`.
- Повні ACF-групи з усіма секціями сторінки — `acf-json/group_logika_course.json`, `group_logika_camp.json`.
- Готова логіка «розкласти курси по вікових блоках каталогу» — `scripts/seed-tilda-courses.php:117` (`logika_sync_tilda_course_catalog()`), і аналог для таборів у `scripts/seed-tilda-summer-camps.php:179`. Але це разові CLI-скрипти, не хуки.
- Квік-лінк «+ Додати новий курс із готовою структурою» — `src/AdminUi.php:207` (веде на порожній `post-new.php`, без шаблону).

## Чого немає (це і є робота)

1. **Дублювання поста.** Ніде немає clone/duplicate для `course`/`camp` (`findDuplicateCity` — це про міста, інше).
2. **Автопотрапляння в каталог.** Картка НЕ з'являється сама. Списки курують вручну:
   - `/it-courses/` → репітер `it_courses_age_categories[].courses` (relationship), рендер `themes/logika-theme/src/PageContent.php:466`;
   - `/english-courses/` → `english_courses_featured_courses`, рендер `PageContent.php:906`;
   - `/camps/` → `camp_archive_formats` + фільтр `camp_is_active`, `template-parts/components/camp-modal.php:7`.
   `archive-course.php` просто редіректить на ту саму курувану сторінку.
3. **Немає перевірки готовності** картки перед публікацією (обов'язкові лише `course_direction`/`course_format`, у табора — `camp_is_active`).
4. **Застарілі тести** `tests/course-camp-architecture.php` і `tests/camp-archive.php` посилаються на неіснуючі `archive-camp.php` / `Logika_Theme_Camp_Archive` — їх треба привести до реальної архітектури, інакше нові тести будуть у тому ж болоті.

## План реалізації

### Етап 1. Движок дублювання — `src/PostDuplicator.php`

- `PostDuplicator::duplicate(int $source_id): int` — створює чернетку:
  - копіює `post_title` (+ « (копія)»), `post_content`, `post_excerpt`, `menu_order`; `post_status = draft`, `post_name` не копіюємо (WP згенерує);
  - копіює **всі** meta, крім службових (`_edit_lock`, `_edit_last`, `_wp_old_slug`, `course_external_id`, `camp_external_id`, `branch_address_hash`) — це переносить і ACF-значення, і `_field_key`-пари;
  - копіює `thumbnail` і всі таксономії (`course_direction`, `age_group`, `learning_format`);
  - **не** дублює медіафайли — картинки переносяться за тими ж attachment ID, менеджер їх замінює;
  - пише `_logika_duplicated_from = $source_id` (для нотиса й аналітики).
- Дозволено лише для `course` і `camp`; перевірка `current_user_can('edit_posts')` + nonce.
- Точки входу:
  1. **Row action** «Дублювати» у списках Курси/Табори (`post_row_actions`);
  2. **Кнопка в редакторі** — meta box збоку «Створити копію цього курсу»;
  3. **Екран «Створити з шаблону»** (див. етап 2).
- Після дублювання — редірект на `post.php?action=edit&new_id=...` і admin notice: «Створено копію «X». Замініть назву, тексти й картинки — і публікуйте.»

### Етап 2. Вибір шаблону — `src/TemplatePicker.php`

- Поле `logika_is_template` (true_false, вкладка «Службове») в групах курсу й табора: «Використовувати як шаблон для нових». Колонка-галочка в списку постів.
- Сабменю «Курси → Створити з шаблону» і «Табори → Створити з шаблону»: сітка карток (мініатюра + назва + напрям/сезон), спочатку позначені як шаблон, далі решта опублікованих. Кнопка «Створити копію» → `PostDuplicator::duplicate()`.
- Для курсів картки згруповані у два таби: **Програмування** / **Англійська** (за термом `english` таксономії `course_direction`) — щоб менеджер не змішував два різні шаблони single-сторінки (`single-course.php:7`).
- Той самий екран лінкується з квік-лінку в `AdminUi::prepareCourseField()` замість поточного порожнього `post-new.php`.

### Етап 3. Автосинк каталогу курсів — `src/CourseCatalogSync.php`

- Винести логіку з `scripts/seed-tilda-courses.php:117` у клас; скрипт викликає клас (жодного дубля логіки).
- Хук `transition_post_status` для `course` (і `acf/save_post` з пріоритетом 25, щоб мати свіжі значення полів):
  - `publish` + `course_direction != english` → додати ID у `it_courses_age_categories[].courses` на сторінці `it-courses` у бакет за `course_age_min/max` (правило бакетів як у скрипті: `<=8 → 7-8`, `>=14 → 14-17`, `max<=11 → 9-11`, інакше `12-14`); ідемпотентно, порядок наявних карток не міняємо, новий курс — у кінець бакета;
  - `publish` + напрям `english` → додати в `english_courses_featured_courses`;
  - вихід із `publish` (`draft`/`trash`/`private`) → прибрати ID з відповідних списків;
  - зміна віку/напряму на вже опублікованому — перекласти в інший бакет/список.
- Поле-запобіжник `course_show_in_catalog` (true_false, за замовчуванням **так**, вкладка «Каталог») — щоб можна було опублікувати курс і не показувати його в каталозі. Значення `false` → синк прибирає ID.
- Ручне редагування репітера каталогу лишається робочим: синк тільки додає/прибирає власний ID, не перезаписує ряд цілком.

### Етап 4. Автосинк архіву таборів — `src/CampArchiveSync.php`

- Те саме для `camp`: `publish` + `camp_is_active = 1` → додати в `camp_archive_formats` сторінки `/camps/`; `camp_is_active = 0`, чернетка або кошик → прибрати.
- Порядок — за `camp_start_date`, потім за `menu_order`.
- Полагодити звертання `update_field(..., 'camp_archive')` зі `scripts/seed-tilda-summer-camps.php:179`: резолвити реальний ID сторінки через `get_page_by_path('camps')`, як у курсах.
- Табір із заповненим `camp_related_course` додатково лінкується з картки курсу (перевірити рендер; якщо звʼязок не рендериться — окремий тікет, не блокує).

### Етап 5. UX редактора: «готово до публікації»

- Meta box «Чек-лист картки» збоку в редакторі курсу/табора: назва, `*_card_image`, `*_card_description`, вік/дати, напрям/формат, головний банер — зелена/сіра галочка + якір на потрібну вкладку.
- `acf/validate_save_post` для `course`/`camp`: при переході в `publish` вимагати `card_image` + `card_description` (+ `course_age_min/max` для курсу, `camp_start_date/end_date` для табора). Для чернетки — не блокувати.
- Після успішної публікації — admin notice з прямим посиланням «Подивитись картку в каталозі» на `/it-courses/#...`, `/english-courses/`, `/camps/`.
- У ACF-групах курсу й табора додати перше поле-`message` з коротким гайдом «Як створити курс за 10 хвилин» — за зразком `tests/city-editor-experience.php`.

### Етап 6. Тести

Запуск: `ddev exec php /var/www/html/tests/<name>.php` (див. `docs/testing.md`).

- `tests/post-duplicator.php` — дублюємо курс-фікстуру: збіг ACF-полів і термів, статус `draft`, не скопійовано `course_external_id`, thumbnail той самий.
- `tests/course-catalog-sync.php` — публікація курсу 9 років → зʼявився у бакеті `9-11`; повторна публікація не дублює; переведення в чернетку прибирає; курс з `english` іде в `english_courses_featured_courses`, а не в it-каталог; ручні картки бакета не зникли.
- `tests/camp-archive-sync.php` — публікація активного табора додає в `camp_archive_formats`, `camp_is_active=0` прибирає; переписати наявний `tests/camp-archive.php` під реальну архітектуру (прибрати неіснуючий `Logika_Theme_Camp_Archive`).
- Актуалізувати `tests/course-camp-architecture.php`: прибрати перевірки `archive-camp.php` / options-сторінки `logika-camp-archive`, додати перевірку нових полів (`logika_is_template`, `course_show_in_catalog`) і квік-лінку на екран шаблонів.
- `tests/acf-editor-contract.php` має проходити для нових полів (`instructions` обовʼязкові, image-поля з `return_format=id`).

### Етап 7. Документація

- `docs/content-model.md` — розділи 4.3/4.4: нові поля + опис автосинку; прибрати згадку неіснуючого `archive-camp.php`.
- Короткий гайд для менеджера `docs/editor-guide-course.md`: 6 кроків зі скріншотами-плейсхолдерами (обрати шаблон → назва → картинка картки → тексти → вік/дати → опублікувати).

## Порядок робіт і залежності

1 → 2 (дублювання перед екраном вибору), 3 і 4 паралельні й незалежні від 1–2, 5 після 3–4 (нотиси посилаються на каталог), 6 разом із кожним етапом, 7 в кінці.

Мінімальний корисний зріз, якщо треба швидко: **етапи 1 + 3 + 4** — це вже «скопіював курс, опублікував, картка в каталозі».

## Ризики

- **Перезапис ручної курації каталогу.** Мітигація: синк оперує тільки власним ID поста, ніколи не переписує весь репітер; є `course_show_in_catalog`.
- **Спільні attachment ID.** Якщо менеджер відредагує (не замінить) картинку в копії — зміниться і в оригіналі. Мітигація: у нотисі й гайді явно писати «замінюйте картинку, а не редагуйте».
- **`acf/save_post` vs `transition_post_status` порядок.** Синк вішати на `acf/save_post` з пріоритетом > 20 (після `AdminUi::saveBranchHash`) + окремо на `transition_post_status` для трешу/відновлення поза редактором.
- **Табір `public=false`** (`ContentTypes.php:40`) — single-сторінка табора доступна лише за прямим посиланням. Перевірити в рантаймі перед етапом 4; якщо картка з модалки веде в 404, це блокер і треба відкривати CPT.
