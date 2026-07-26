## which css file do i edit?

quick notes so i don't forget where stuff is:

- a theme color (autumn/winter/etc) -> theme-*.css, pick the right one
- a color everywhere, all themes -> variables.css (this is just the default though, see below)
- fonts, text color, page width, the reset -> base.css
- buttons (class="btn") -> buttons.css
- top nav bar -> header.css
- footer -> footer.css
- the big banner on Home/About/Help -> hero.css for layout, theme-*.css for the actual background
- product cards -> product-card.css
- page sections / "Our Values" boxes -> sections.css
- coffee/tea tabs on home page -> tabs.css
- any table -> tables.css
- form inputs / alert boxes -> forms.css
- admin dashboard tiles -> dashboard.css
- anything admin-only -> admin.css
- small margin tweak -> utilities.css, there's probably already a class like mt-sm

## why so many files

every page loads ALL of these every time (see includes/header.php), in this order:
variables -> theme -> base -> (buttons, header, footer, hero, sections, tabs,
tables, forms, product-card, dashboard, admin) -> utilities

so each file just owns one part of the page instead of one huge css file.
more files but easier to find stuff.

## the theme/color thing that confused me at first

colors aren't hardcoded in buttons.css etc, they use variables like
var(--color-primary). that variable gets set twice: once in variables.css
(the default) and then again in each theme-*.css with different values.
whichever theme.css loads last wins, and that's literally the whole theme
switcher -- it's not doing anything fancy, it just loads a different css
file depending on what's saved in the db.

so if i want to change one theme's color, edit that theme file. if i add a
brand new variable, i need to give it a value in variables.css AND all 4
theme files or it'll only work in 3 of them.
