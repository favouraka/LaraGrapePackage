# Upgrading to LaraGrape 1.5

## Package changes (v1.5.0)

- Full GrapesJS editor sync: Alpine in canvas, dynamic forms, tech stack traits, `syncToFormBeforeSubmit`
- `DynamicBlockDataService` + `block_dynamic_data` save pipeline
- `form-blocks.js` for AJAX form submission in canvas and on the live site
- Optional portfolio module: `php artisan laragrape:setup --portfolio` then `LARAGRAPE_PORTFOLIO=true`

## Fresh install test

From the package root:

```bash
./test_laragrape_install.sh
```

## streatsdesign consumption

```bash
composer require streats22/laragrape:^1.5
php artisan laragrape:update --assets --services --views --force
# if using portfolio:
php artisan laragrape:update --portfolio --force
npm install && npm run build
```

Remove redundant overrides after update:

- `resources/js/grapesjs-editor.js`
- `resources/js/form-blocks.js`
- `app/Services/DynamicBlockDataService.php`
- `app/Support/TechStackRegistry.php` (unless extending via config)

Set in `.env`:

```
LARAGRAPE_PORTFOLIO=true
```
