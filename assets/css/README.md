# CSS map — "I want to change X, which file do I open?"

| I want to change...                                              | Open this file       |
|-------------------------------------------------------------------|-----------------------|
| A color, for a specific season/theme (autumn, winter, etc.)       | `theme-*.css` (the one matching that theme) |
| A color, everywhere, in every theme at once                       | `variables.css` (only if you want to change the *fallback*/default — see note below) |
| Fonts, base text color, page width, the CSS reset                 | `base.css` |
| Any `<button>` or `class="btn"` link                               | `buttons.css` |
| The top nav bar (logo, menu links, cart icon, mobile hamburger)    | `header.css` |
| The footer (columns, links, embedded map)                         | `footer.css` |
| The big banner at the top of Home/About/Help pages                | `hero.css` (layout) + the matching `theme-*.css` (background image/color) |
| Product cards (Home "Featured", Shop grid, product detail page)   | `product-card.css` |
| Padded page sections, section titles, the "Our Values" icon boxes | `sections.css` |
| The Coffee/Tea tab switcher on the home page                      | `tabs.css` |
| Any `<table>` (cart, checkout, admin lists)                       | `tables.css` |
| Form inputs, labels, validation messages, alert banners           | `forms.css` |
| Admin dashboard stat tiles / chart cards                          | `dashboard.css` |
| Anything only on `/admin/*` pages                                 | `admin.css` |
| A one-off spacing tweak (margin) on a single element               | `utilities.css` (add/reuse a small `.mt-*`/`.mb-*` class instead of a new rule) |

## Why there are so many files

Every page loads **all** of these, always, in this fixed order (see `includes/header.php`):

```
variables.css → theme-*.css (whichever is active) → base.css
→ buttons, header, footer, hero, sections, tabs, tables, forms,
  product-card, dashboard, admin (any order among these 11)
→ utilities.css
```

Each file only owns **one part of the page** (its header comment says which), so you never have to guess where a rule that changes the footer might be hiding in the button styles. The tradeoff is more files instead of one giant stylesheet.

## How colors actually work (the part that trips people up)

Colors are never hardcoded in the component files (`buttons.css`, `header.css`, etc.) — they always reference a variable like `var(--color-primary)`. That variable's actual value is defined **twice**:

1. Once in `variables.css`, as a fallback/default.
2. Again in each `theme-*.css` file, using the *same variable names* but different color values.

Whichever theme file the site currently has active loads **after** `variables.css`, so its values win. That's the entire theme-switching mechanism — nothing else changes when you switch themes in Admin → Site Template, just which `theme-*.css` file loads last.

**So: if you want to change a color for one theme only, edit that `theme-*.css` file. If you want to add a brand-new color variable for use across the whole site, add it to `variables.css` *and* give it a matching value in all four `theme-*.css` files** (otherwise it'll only have a value in whichever theme doesn't override it, and look broken in the other three).
