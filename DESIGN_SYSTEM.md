# KindergartenOrganizer Design System Guidelines

An Apple-polish-inspired design system for a calm, intuitive, cross-platform kindergarten management application.

---

## 1. Core Design Principles

1. **Calm and Spacious** -- Avoid visual clutter. Use whitespace (`--spacing-*` tokens) to separate sections and create breathing room. Every element should have room to breathe.
2. **Typography-First** -- Text hierarchy drives visual order. Headlines, subheadlines, and body copy must be clear and readable using the defined font scale.
3. **Subtle Motion** -- Use smooth transitions (`--transition-fast`, `--transition-normal`) for feedback on hover, focus, and click. Avoid flashy or decorative animations.
4. **Consistency** -- All elements share the same visual language: spacing scale, color palette, border radius tokens, shadow levels, and motion speed.
5. **Accessibility by Default** -- High contrast, clear focus states, readable text, touch-friendly interactive elements (minimum 44px targets).
6. **Device-Agnostic** -- Must work equally well on mobile (iOS & Android), desktop, tablets, and lower-end devices. Mobile-first responsive design.

---

## 2. Typography

### Font Stack

```css
--font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
```

System fonts ensure compatibility across all devices without external font downloads. Apple devices get San Francisco, Windows gets Segoe UI, Android/Linux gets Roboto.

### Type Scale

| Token | Size | Usage |
|-------|------|-------|
| `--font-size-xs` | 0.75rem (12px) | Captions, helper text, badges |
| `--font-size-sm` | 0.875rem (14px) | Secondary text, table cells, sidebar items |
| `--font-size-base` | 1rem (16px) | Body text, form inputs, list items |
| `--font-size-lg` | 1.125rem (18px) | Emphasized body, card titles |
| `--font-size-xl` | 1.25rem (20px) | Section headings (H3) |
| `--font-size-2xl` | 1.5rem (24px) | Page sub-headings (H2) |
| `--font-size-3xl` | 1.875rem (30px) | Page headings (H1) |
| `--font-size-4xl` | 2.25rem (36px) | Hero/display text (rare) |

### Font Weights

| Token | Weight | Usage |
|-------|--------|-------|
| `--font-weight-normal` | 400 | Body text, descriptions |
| `--font-weight-medium` | 500 | Subtle emphasis, navigation items |
| `--font-weight-semibold` | 600 | Card titles, form labels, buttons |
| `--font-weight-bold` | 700 | Page headings, critical emphasis |

### Line Heights

| Token | Value | Usage |
|-------|-------|-------|
| `--line-height-tight` | 1.25 | Headings, compact UI elements |
| `--line-height-normal` | 1.5 | Body text, form inputs |
| `--line-height-relaxed` | 1.75 | Long-form content, help text |

### Typography Rules

- Establish clear hierarchy: H1 > H2 > H3 > Body > Caption.
- Prioritize legibility over style; use weight changes (not decoration) for emphasis.
- On mobile, the font size preference (`data-font-size="small|medium|large"`) adjusts the base scale to reduce eye strain.
- Never go below `--font-size-xs` for any visible text.

---

## 3. Color & Contrast

### Primary Palette

| Token | Light Mode | Dark Mode | Usage |
|-------|-----------|-----------|-------|
| `--color-primary` | `#4F46E5` (Indigo) | Same | Primary actions, active states, links |
| `--color-primary-dark` | `#4338CA` | Same | Hover states on primary elements |
| `--color-primary-light` | `#6366F1` | Same | Focus rings, highlights |
| `--color-primary-bg` | `#EEF2FF` | `rgba(79,70,229,0.15)` | Primary-tinted backgrounds |

The primary color is user-customizable via the settings page (16 presets + custom hex picker). All primary-derived variables update dynamically via inline CSS overrides.

### Status Colors

| Role | Base | Dark | Background (Light) | Background (Dark) |
|------|------|------|-------------------|-------------------|
| Success | `#22C55E` | `#16A34A` | `#F0FDF4` | `rgba(34,197,94,0.12)` |
| Warning | `#F59E0B` | `#D97706` | `#FFFBEB` | `rgba(245,158,11,0.12)` |
| Danger | `#EF4444` | `#DC2626` | `#FEF2F2` | `rgba(239,68,68,0.12)` |
| Info | `#3B82F6` | `#2563EB` | `#EFF6FF` | `rgba(59,130,246,0.12)` |

### Neutral Palette

11-step gray scale from `--color-gray-50` (`#F9FAFB`) to `--color-gray-900` (`#111827`), plus `--color-white` and `--color-black`. In dark mode, the gray scale inverts: `--color-white` becomes `#1a1a2e`, `--color-gray-50` becomes `#16162a`, etc.

### Color Rules

- **Limit active colors**: Use neutrals as the dominant surface color. Accents (primary, status) appear only for calls-to-action, feedback, or emphasis.
- **WCAG AA minimum**: All text/background pairings must meet 4.5:1 contrast ratio for normal text, 3:1 for large text.
- **Never rely on color alone**: Use icons, labels, or patterns alongside color to convey meaning (e.g., status badges include text labels, not just colored dots).
- **Cross-device consistency**: Use hex and rgba values (not system colors) so appearance is consistent on Apple and non-Apple devices.
- **Dark mode**: Every component must have dark mode overrides. Use `[data-theme="dark"]` CSS scope. Dark backgrounds use reduced-opacity rgba for status tints.

---

## 4. Spacing & Layout

### Spacing Scale

Based on a **4px base unit** (multiples of 0.25rem):

| Token | Value | Pixels | Common Usage |
|-------|-------|--------|-------------|
| `--spacing-0` | 0 | 0 | Reset |
| `--spacing-1` | 0.25rem | 4px | Tight gaps (icon-to-text) |
| `--spacing-2` | 0.5rem | 8px | Inline element gaps, compact padding |
| `--spacing-3` | 0.75rem | 12px | Small component padding |
| `--spacing-4` | 1rem | 16px | Standard padding, form field gaps |
| `--spacing-5` | 1.25rem | 20px | Card padding |
| `--spacing-6` | 1.5rem | 24px | Section spacing |
| `--spacing-8` | 2rem | 32px | Large section gaps |
| `--spacing-10` | 2.5rem | 40px | Page section separation |
| `--spacing-12` | 3rem | 48px | Major layout gaps |
| `--spacing-16` | 4rem | 64px | Hero/header spacing |

### Layout Dimensions

| Token | Value | Component |
|-------|-------|-----------|
| `--rail-width` | 56px | Icon rail (left navigation bar) |
| `--ctx-sidebar-width` | 200px | Context sidebar (expandable) |
| `--header-height` | 60px | Top header bar |
| `--container-max-width` | 1200px | Main content max width |

### Layout Rules

- **Modular spacing**: Always use spacing tokens. Never hardcode pixel values for margins/padding.
- **Generous whitespace**: Especially around text blocks and interactive elements. Space signals calm.
- **Single-column on mobile**: Multi-column grids collapse at `max-width: 768px`.
- **Content scales naturally**: Use `max-width` and flexible containers, not fixed widths, so content adapts without breaking.
- **App shell**: Icon rail (56px, fixed left) + context sidebar (200px, collapsible) + main content area. On mobile, sidebar becomes an overlay.

---

## 5. Components & Patterns

### Buttons

- **Minimum touch target**: 44px height for all interactive buttons.
- **Variants**: `.btn-primary` (filled, primary color), `.btn-secondary` (outlined/subtle), `.btn-danger` (destructive actions), `.btn-ghost` (text-only, no background).
- **Consistent shape**: Use `--radius-lg` (0.5rem) for standard buttons, `--radius-full` for icon-only circular buttons.
- **States**: Default, hover (`--color-primary-dark`), focus (visible ring), active (slight scale), disabled (reduced opacity).
- **Padding**: `--spacing-2` vertical, `--spacing-4` horizontal minimum.
- **Font**: `--font-weight-semibold`, `--font-size-sm` or `--font-size-base`.

### Forms & Inputs

- **Labels**: Always visible above the input, `--font-weight-semibold`, `--font-size-sm`.
- **Inputs**: Full-width within their container, `--spacing-3` padding, `--radius-lg` border radius.
- **Focus**: Clear focus ring using `--color-primary-light` with visible outline. Never remove the default focus indicator without providing a replacement.
- **Spacing**: `--spacing-4` gap between form groups.
- **Help tooltips**: `.help-tooltip` spans with `data-help` attribute on form labels for contextual guidance.
- **Validation**: Error messages appear below inputs in `--color-danger` with `--font-size-sm`.

### Cards & Containers

- **Elevation via shadows**: Use `--shadow-sm` for flat cards, `--shadow-md` for elevated cards, `--shadow-lg` for overlays. No visible borders -- shadows alone define separation.
- **Border radius**: `--radius-2xl` (1rem) for cards, giving a soft, modern feel.
- **Padding**: `--spacing-5` (1.25rem) internal padding minimum.
- **Background**: `--color-white` (adapts to dark mode automatically).
- **Dashboard cards**: Fixed 340px height with overflow hidden (`.dash-card-fixed`).

### Navigation

- **Icon rail**: 56px fixed left bar with icon-only buttons. Active state indicated by primary color tint.
- **Context sidebar**: 200px slide-in panel with section-specific links. Collapsible via hamburger button. State persisted in localStorage.
- **Header**: 60px top bar with search trigger (Ctrl+K), help toggle, and user dropdown.
- **Command palette**: Centered modal overlay (`.search-palette-overlay`) for global search. Keyboard navigable (arrow keys, Enter, Escape).
- **Quick create**: Plus button popup at bottom of icon rail for fast entity creation.
- **Responsive**: On mobile (`max-width: 768px`), icon rail collapses, sidebar becomes overlay.

### Feedback & Alerts

- **Flash messages**: `.alert-success`, `.alert-warning`, `.alert-danger`, `.alert-info` with status background colors and matching icons.
- **Transitions**: Elements entering/leaving use `--transition-normal` (300ms ease).
- **No sudden jumps**: Layout should not shift when alerts appear/disappear.

### Modals

- **Backdrop**: Semi-transparent overlay (`rgba(0,0,0,0.5)`).
- **Card style**: `--shadow-xl`, `--radius-2xl`, centered vertically and horizontally.
- **Close**: Escape key and backdrop click both dismiss. Visible close button in corner.
- **Z-index layering**: Modals at z-index 1000+, cropper modal at 10000 (above search palette).

---

## 6. Border Radius

| Token | Value | Usage |
|-------|-------|-------|
| `--radius-sm` | 0.25rem (4px) | Badges, small chips |
| `--radius-md` | 0.375rem (6px) | Form inputs, inline elements |
| `--radius-lg` | 0.5rem (8px) | Buttons, dropdowns |
| `--radius-xl` | 0.75rem (12px) | Cards (secondary) |
| `--radius-2xl` | 1rem (16px) | Primary cards, modals, overlays |
| `--radius-full` | 9999px | Circular buttons, avatars, pills |

### Radius Rules

- Prefer `--radius-2xl` for prominent containers (cards, modals) -- this is the signature "Apple-like" softness.
- Nested elements should use smaller radii than their parent (e.g., button inside a card uses `--radius-lg` while the card uses `--radius-2xl`).
- Never mix sharp corners and rounded corners on the same component.

---

## 7. Shadows & Elevation

| Token | Value | Usage |
|-------|-------|-------|
| `--shadow-sm` | `0 1px 2px rgba(0,0,0,0.05)` | Subtle lift: inputs, flat cards |
| `--shadow-md` | `0 4px 6px rgba(0,0,0,0.1), 0 2px 4px rgba(0,0,0,0.06)` | Standard cards, dropdowns |
| `--shadow-lg` | `0 10px 15px rgba(0,0,0,0.1), 0 4px 6px rgba(0,0,0,0.05)` | Floating panels, popovers |
| `--shadow-xl` | `0 20px 25px rgba(0,0,0,0.1), 0 10px 10px rgba(0,0,0,0.04)` | Modals, overlays |

### Shadow Rules

- Shadows replace borders as the primary method of visual separation. This is a core Apple-inspired design choice.
- In dark mode, shadow opacity increases to remain visible against dark backgrounds.
- Elevation implies interactivity: higher shadow = more prominent/interactive element.
- Avoid stacking shadows (e.g., a card with `--shadow-md` inside a panel with `--shadow-lg` is fine, but don't add extra shadows to the card).

---

## 8. Motion & Transitions

| Token | Duration | Usage |
|-------|----------|-------|
| `--transition-fast` | 150ms ease | Hover states, button feedback, icon changes |
| `--transition-normal` | 300ms ease | Panel slides, modal open/close, content transitions |
| `--transition-slow` | 500ms ease | Page-level transitions, complex animations (rare) |

### Motion Rules

- **Short and smooth**: All hover/focus/active states use `--transition-fast` (150ms).
- **Panel transitions**: Sidebar collapse/expand, help panel slide, modal appearance use `--transition-normal` (300ms).
- **No bouncing or looping**: Avoid spring physics, infinite animations, or decorative motion.
- **Motion reinforces understanding**: A sidebar sliding in communicates "new content area". A card lifting on hover communicates "this is interactive".
- **Performance on mobile**: Keep motion minimal. Prefer `transform` and `opacity` for GPU-accelerated transitions. Avoid animating `width`, `height`, `margin`, or `padding`.
- **Respect user preferences**: Honor `prefers-reduced-motion` media query for users who disable animations.

---

## 9. Accessibility & Usability

### Touch Targets

- All interactive elements must be at least **44px x 44px** touch target (icon rail buttons, form controls, action buttons).
- Spacing between adjacent touch targets should be at least `--spacing-2` (8px).

### Focus States

- Every interactive element must have a visible focus indicator.
- Use `outline` with `--color-primary-light` for keyboard focus rings.
- Never use `outline: none` without a replacement focus style.

### Screen Readers & ARIA

- All images require meaningful `alt` text (or `alt=""` for decorative images).
- Interactive elements without visible text need `aria-label`.
- Dynamic content updates use `aria-live` regions.
- Modal dialogs trap focus and use `role="dialog"` with `aria-modal="true"`.

### Color Independence

- Status indicators use icons + text labels alongside color.
- Form validation errors include text messages, not just red borders.
- Charts or visual data should be distinguishable without color (patterns, labels).

### Keyboard Navigation

- Full keyboard navigation: Tab through interactive elements, Enter to activate, Escape to dismiss.
- Command palette: `Ctrl+K` / `Cmd+K` to open, arrow keys to navigate results, Enter to select.
- Help panel and sidebar: keyboard accessible toggle buttons.

---

## 10. Responsive & Cross-Device Guidelines

### Breakpoints

| Breakpoint | Width | Target |
|-----------|-------|--------|
| Mobile | `max-width: 480px` | Small phones |
| Tablet | `max-width: 768px` | Tablets, large phones |
| Desktop small | `max-width: 1024px` | Small laptops, tablets in landscape |
| Desktop | `> 1024px` | Standard desktops |

### Mobile-First Approach

- Start with the smallest screen and scale up using `min-width` media queries for progressive enhancement.
- All CSS defaults should produce a usable mobile layout without any media query.

### Layout Adaptations

| Component | Desktop | Tablet | Mobile |
|-----------|---------|--------|--------|
| Icon rail | 56px fixed left | Collapsible | Hamburger overlay |
| Context sidebar | 200px slide-in | Overlay | Hidden by default |
| Content grid | Multi-column | 2 columns | Single column |
| Tables | Full width | Horizontal scroll | Horizontal scroll |
| Form layouts | Multi-column groups | 2 columns | Single column |
| Bulk action bars | Horizontal | Horizontal | Stacked vertical |
| Dashboard | Two-column | Two-column | Single column |
| Cards | Grid layout | 2-column grid | Full-width stack |

### Performance

- No external font files -- system font stack loads instantly.
- Minimize CSS specificity to reduce rendering overhead.
- Use `will-change` sparingly and only on elements that actually animate.
- Test on Android, Windows, Linux, and older browsers -- not just Apple devices.
- Background patterns (dots, stars, hearts, clouds, grid, waves) use lightweight CSS gradients or inline SVGs, not image files.

### Fluid Sizing

- Use `rem` units for all sizing to respect user font size preferences.
- The `data-font-size` attribute scales the root font size for small (0.9x), medium (1x), and large (1.1x) modes.
- Avoid fixed pixel widths for content containers; use `max-width` with percentage fallbacks.

---

## 11. Dark Mode

### Implementation

- **Three states**: System (follows `prefers-color-scheme`), Light (forced), Dark (forced).
- **Controlled by**: `data-theme="light|dark"` on `<html>`, `data-dark-mode-pref="system|light|dark"` for preference tracking.
- **Persistence**: Saved to `storage/preferences.php` via AJAX POST to `/settings/dark-mode`.

### Dark Mode Color Strategy

- All CSS variables are overridden under `[data-theme="dark"]` scope.
- Backgrounds invert: white surfaces become deep blue-black (`#1a1a2e`).
- Status color backgrounds use reduced-opacity rgba instead of solid pastels.
- Text colors invert: dark text becomes light, maintaining the same contrast ratios.
- Shadows use increased opacity to remain visible on dark surfaces.

### Dark Mode Checklist for New Components

When adding any new component, ensure:

1. All color references use CSS variables (never hardcoded hex in component CSS).
2. Background, text, and border colors have `[data-theme="dark"]` overrides.
3. Shadows are visible on dark backgrounds.
4. Images and icons remain distinguishable (consider filter or opacity adjustments).
5. Status colors maintain meaning and contrast in both modes.

---

## 12. Customization System

### User-Configurable Options

| Setting | Mechanism | Storage |
|---------|-----------|---------|
| Theme color | 16 presets + custom hex picker | `preferences.php → theme_color` |
| Background pattern | 6 patterns (dots, stars, hearts, clouds, grid, waves) + none | `preferences.php → theme_pattern` |
| Dark mode | System / Light / Dark toggle | `preferences.php → dark_mode_preference` |
| Font size | Small / Medium / Large | `preferences.php → font_size` |
| Compact sidebar | On / Off | `preferences.php → compact_sidebar` |
| Items per page | Numeric | `preferences.php → items_per_page` |
| Default view | Grid / List | `preferences.php → default_view` |
| Language | German / English | `preferences.php → language` |

### Theme Color Integration

The primary color and its variants are injected as inline `<style>` overrides in the main layout:

```html
<style>
:root {
    --color-primary: #4F46E5;
    --color-primary-dark: /* derived darker */;
    --color-primary-light: /* derived lighter */;
    --color-primary-bg: /* derived tint */;
}
</style>
```

New components should only reference `--color-primary` and its variants -- never hardcode the indigo hex value.

### Background Patterns

Applied via `body[data-pattern="..."]` attribute, using CSS `background-image` with lightweight gradients and inline SVGs. Patterns must be subtle enough not to interfere with content readability in both light and dark modes.

---

## 13. Component Checklist

When building or modifying any component, verify against this checklist:

### Visual Consistency

- [ ] Uses only design tokens (spacing, color, radius, shadow, font variables)
- [ ] No hardcoded pixel values for spacing or sizing
- [ ] No hardcoded color hex values
- [ ] Border radius matches component role (see Section 6)
- [ ] Shadow level matches elevation role (see Section 7)

### Dark Mode

- [ ] All colors reference CSS variables
- [ ] Has `[data-theme="dark"]` overrides where needed
- [ ] Tested visually in both light and dark mode

### Responsive

- [ ] Works at all breakpoints (480px, 768px, 1024px, 1200px+)
- [ ] Touch targets are at least 44px
- [ ] No horizontal scrolling on mobile (unless intentional, e.g., tables)
- [ ] Text remains readable at all sizes

### Accessibility

- [ ] Focus state is visible for keyboard users
- [ ] Color is not the sole indicator of meaning
- [ ] Interactive elements have accessible labels
- [ ] Contrast ratio meets WCAG AA (4.5:1 for text, 3:1 for large text)

### Motion

- [ ] Uses `--transition-fast` or `--transition-normal` (not custom durations)
- [ ] No decorative animations
- [ ] Respects `prefers-reduced-motion`

### Internationalization

- [ ] All visible text uses `__()` translation function
- [ ] Layout accommodates longer strings (German is ~30% longer than English)
- [ ] No text embedded in images or SVGs

---

## 14. Design Governance

### Adding New Design Tokens

1. New tokens must follow the existing naming convention (`--category-variant`).
2. Every new token must have a dark mode override.
3. Document the token in this file with its value and intended usage.
4. Tokens should be defined in the `:root` block of `style.css`.

### Adding New Components

1. Check this design system first for an existing component that can be reused or extended.
2. Follow the component checklist (Section 13) before integration.
3. Add the component's CSS in the appropriate section of `style.css` with a section comment header.
4. Test on: desktop Chrome/Firefox/Safari, mobile Safari (iOS), mobile Chrome (Android), and at least one lower-end device.

### Regular Audits

- Periodically check all breakpoints for layout issues.
- Run contrast checks on new color pairings.
- Verify dark mode coverage for recently added components.
- Review touch target sizes when adding new interactive elements.

---

## Summary

The KindergartenOrganizer should feel:

- **Calm, intuitive, and polished** -- like Apple's ecosystem, but fully cross-platform.
- **Clear hierarchy** with readable text and generous breathing room.
- **Interactive elements** that are consistent, touch-friendly, and responsive.
- **Motion** that is subtle, purposeful, and fast.
- **Easy to maintain and expand** using design tokens and the component checklist.

All visual decisions flow from the CSS custom properties defined in `:root`. Consistency comes from using these tokens everywhere and never hardcoding values. Dark mode, customization, and responsiveness are first-class concerns, not afterthoughts.

---

*Last updated: 2026-02-27*
