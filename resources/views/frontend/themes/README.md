# Public site theme packs

Path: `resources/views/frontend/themes/{pack}/`

```
themes/
  klasik/layouts/app.blade.php
         layouts/partials/{head,header,footer,script}.blade.php
         pages/anasayfa.blade.php   (optional)
  modern/
  minimal/
  ocean/
  sicak/
```

- `theme_layout()` → `frontend.themes.{pack}.layouts.app`
- `theme_view('pages.x')` → theme page or fallback `frontend.pages.x`
- CSS: `public/css/themes/{id}.css`
- Config: `config/themes.php` → catalog[].layout = pack folder name

Yeni tema: config + css + themes/{id}/layouts/app.php + partials (+ pages)
