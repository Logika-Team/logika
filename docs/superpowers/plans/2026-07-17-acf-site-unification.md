# ACF Site Unification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Привести всі публічні сторінки до редакторського стандарту ACF головної сторінки без зміни URL, дизайну або публічних API.

**Architecture:** `logika-core` зберігає Local JSON і глобальні options, а `logika-theme` виводить секції через явні template-parts. Системні сторінки мають фіксований порядок; лише майбутній generic template використовує контрольований Flexible Content.

**Tech Stack:** WordPress 7.0.1, PHP 8.3, ACF Pro 6.8.5, DDEV.

## Global Constraints

- Усі labels, instructions і публічний текст — українською.
- Не змінювати наявні URL, CSS-класи, REST-контракти та Home image override.
- Зберігати редакторські значення; мігрувати лише порожні або відомі test/placeholder значення.
- Image/Gallery повертають attachment ID і показують `medium` preview.
- Після кожного етапу: PHP-тести, live smoke, ACF duplicate/preview audit і JSON dry-run.

### Task 1: Runtime baseline

- [x] Перевірити linked worktree, DDEV, WordPress і ACF Pro.
- [x] Створити DDEV snapshot `pre-acf-unification-20260717`.
- [x] Встановити MCP Adapter, виконати runtime/Local JSON audits і синхронізувати `article_author`.
- [x] Виправити знайдені baseline-регресії routing і Telegram share.

### Task 2: Shared editor contract

- [x] Додати RED-тест унікальності keys, instructions та Image/Gallery preview.
- [x] Нормалізувати Local JSON, Global Options, menu locations і фільтрацію Relationship.
- [x] Додати спільні template-parts без дублювання розмітки.

### Task 3: Fixed Pages and legal content

- [x] Замінити `*_page_texts` секційними полями About, IT, English, FAQ і Media Center.
- [x] Додати спільну legal group і renderer.
- [ ] Ідемпотентно перенести поточні значення та assets.

### Task 4: Course and Camp

- [x] Розширити Course/Camp groups фіксованими секціями та preview.
- [x] Підключити ACF renderer до archive/single templates.
- [x] Додати Camp Archive Options і швидкий перехід до створення Course.

### Task 5: City, editorial and generic Pages

- [x] Додати city override з Home/Global fallback.
- [x] Завершити Blog/Article editor UX.
- [x] Додати generic template з дев'ятьма затвердженими Flexible layouts.

### Task 6: Cutover and verification

- [ ] Припинити читання legacy `*_page_texts`, зберігши стару meta для rollback.
- [ ] Двічі запустити міграцію та підтвердити нуль змін на другому запуску.
- [ ] Виконати повний PHP/browser/live smoke і оновити документацію.
