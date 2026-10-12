# Jednotka Elementor

Starter WordPress plugin for this project's custom Elementor widgets. Requires
Elementor Free; Elementor Pro is not required.

## Getting started

1. Paste your reference folder into `examples/`.
2. Activate **Jednotka Elementor** in WordPress under **Plugins** when ready.
3. In the Elementor editor, find **Jednotka Header** under **Jednotka** and drag it
   into a full-width container at the top of your page. Set the container padding
   and gap to zero so the widget controls its own spacing.

## Header widget

Recreates the reference page's top navigation bar: logo, five editable links,
animated action button, and mobile dropdown. No Elementor Pro features are used.

- Click a navigation label or the action button's text in the Elementor preview
  to edit it directly. Changes are saved with Elementor's **Update / Publish**.
- Under **Content → Navigation links**, expand each item to set its URL, text
  color, hover color, font, and responsive size. Items can be added, removed, or
  reordered. Default URLs are suggestions; update them to match your real pages.
- Under **Style → Action button**, set the button's font, size, colors, and padding.
- Under **Content → Logo / media**, replace the bundled logo with an image from
  the Media Library or choose **Uploaded video**. A video has a poster image and
  a play/pause control. It is muted, loops, and respects reduced motion preferences.
- Under **Style → Header layout**, change the background, spacing, logo width,
  navigation gap, and mobile menu colors. The default dark background makes the
  white logo visible; clear it when placing the standalone header over a background.

The dropdown opens at widths of 1024px or less. It supports keyboard navigation,
Escape, and outside click. The desktop action button is hidden at these widths,
matching the reference's mobile layout. In the editor, open the mobile menu to
edit its labels; it stays open when you click its text.

This is a page widget. It does not replace the theme's header or automatically
appear sitewide. For a page without the theme's header, choose **Elementor Canvas**
in the page layout settings. You can copy the widget to other pages as needed.

The default logo and font are copied into `assets/`; the widget does not depend
on reference files in `examples/`.

## Hero widget

Add **Jednotka Hero** to a full-width container with zero container padding and
gap. Navigation is included by default inside the same gradient frame, so no
separate Header widget or container class is needed. It includes two heading lines, five floating
service badges, description, and action button. All assets are bundled in
`assets/`, independently of the examples.

- Click the description, button label, or badge labels in the Elementor preview
  to edit text directly. Heading lines are also inline editable in text mode.
- Under **Content → Hero navigation**, switch the built-in navigation on or off.
  The logo, navigation links, and navigation action button have their own content
  and style sections, with the same image/video and individual text controls as
  the standalone Header. The navigation button is independent of the hero button.
- Each heading line defaults to **Image**, using the original “Marketing na” SVG
  and “Jednotku” PNG artwork from the reference. Replace these through the media
  pickers, or switch **Display as** to **Editable text** or **Uploaded video**.
  Existing widgets saved in text mode retain that choice; switch both heading
  lines to **Image** to use the reference artwork.
- Under **Style**, each heading line, the description, and button have separate
  typography and color controls. Heading media also has responsive width controls.
- Under **Content → Service badges**, each badge has its own label, image/video
  picker, font, size, color, background, and desktop position. You can reorder,
  add, or remove badges. On smaller screens they wrap above the heading.
- **Style → Hero layout and gradient** controls the gradient colors, minimum
  height, spacing, and corners. Default positioning and sizing come directly
  from the reference, including its viewport-height layout. If you add much
  longer content, adjust the spacing/height and check the mobile layout.
- Set a poster when using videos. Videos loop muted; the corner play/pause
  button controls hero videos and badge motion together. Badges float as whole
  pills and respond to pointer movement on desktop, as in the reference. Floating
  is visible in the editor and pauses while typing. Videos start paused in the
  editor; all motion starts paused for visitors who prefer reduced motion.

### Copying the reference design

The hero now uses the reference HTML structure and original class names, with
additional Elementor hooks for editable settings. Its visual CSS is copied from
`examples/css/variables.css`, `examples/css/main.css`, and
`examples/css/pages/home.css` into `assets/css/reference-hero.css`. The copied
rules are scoped to the widget so they do not affect other page sections.
`assets/css/hero.css` contains only Elementor integration adjustments.

After updating the example styles, run this from the plugin folder:

```sh
python3 tools/copy-reference-css.py
```

The hero animation uses the original `examples/js/hero-badges.js` calculations,
adapted to Elementor's initialization and cleanup, plus editing and pause controls.
Keep future elements based on the example markup and styles first, then add
Elementor controls without recreating their design.

For the reference appearance, use image mode for both heading lines and leave
style overrides at their defaults. The copied hero badge breakpoint is 900px;
the integrated navigation breakpoint is 700px, matching the example.

### Place the header over the hero

The Hero now includes navigation automatically. Remove a previously placed
standalone Header from this section to avoid displaying navigation twice.

If you prefer to keep using two separate widgets, disable **Hero navigation →
Show navigation**, then use the earlier shared-container setup:

1. Create a full-width vertical Elementor container with zero padding and gap.
2. In its **Advanced → CSS Classes**, add `jednotka-hero-layout`.
3. Put **Jednotka Header**, followed by **Jednotka Hero**, directly inside it.

The class places the header over the hero and makes its background transparent.
Keep this container's overflow visible so the mobile menu can open fully.
Without the class, the widgets remain separate blocks. This setup works with
Elementor Free and does not need the Pro custom CSS field.

## Structure

- `jednotka-elementor.php`: plugin entry point.
- `includes/class-plugin.php`: dependency check, category, and widget registration.
- `widgets/`: custom widget classes extending `Elementor\Widget_Base`.
- `assets/css/`: widget styles.
- `assets/js/`: widget scripts, when needed.
- `examples/`: your reference material, which is not loaded by the plugin.

## Logo Strip widget

Place **Jednotka Logo Strip** directly below the Hero in a full-width container
with zero padding and gap. Its default markup, two SVG logo strips, 96px height,
64px gaps, edge fades, and 34-second scrolling loop are copied from the reference.
The reference animation is CSS; no carousel library is needed.

- **Content → Logos / media**: replace either strip image, add individual logos,
  reorder/remove items, or choose uploaded videos with poster images. Each item
  has an accessible name, responsive height, and opacity settings. Both loop
  groups update from the same items.
- **Content → Scrolling**: change duration/direction, turn scrolling off, or
  enable pause on hover. Lower duration means faster scrolling.
- **Style → Strip layout**: change background, strip height, spacing, and fades.
- The pause control appears on hover or keyboard focus. Reduced-motion visitors
  get a paused strip. Videos start paused in the editor.
- When replacement logos form a row shorter than the available width, the widget
  adds hidden repeats within both equal loop groups to prevent empty gaps.

The original artwork contains lettering inside images; replace the media to
change those logos. Font/color text controls do not change lettering inside an
image. The CSS copy command above also regenerates `reference-logo-strip.css`.

## Stats & Testimonials widget

Place **Jednotka Stats & Testimonials** below the Logo Strip in a full-width
container with zero padding and gap. It copies the reference's three statistics,
heading, five glass cards, avatar artwork, and responsive layouts. The original
count-up, scroll reveal, and floating card animations are retained.

- Click any number, label, heading, quote, client name, or description to edit it
  directly. Each text has its own color and typography controls in its Content
  section or repeater item, including responsive font sizes.
- **Content → Statistics**: add/reorder statistics and enable count-up. Whole
  numbers with optional suffixes (such as `100+`) animate; other values display
  exactly as entered. Numbers stay at their final values while editing.
- **Content → Testimonials**: add/reorder cards, replace each avatar with an image
  or uploaded video, and change individual card backgrounds, padding, and avatar
  sizes. More than five cards use a grid to avoid overlapping.
- Scroll reveal and floating motion have independent switches. Cards stay fully
  visible in the editor, with floating motion pausing while typing. The corner
  pause button appears on hover or keyboard focus and controls reveal, drift,
  and videos. Videos start paused in the editor; reduced-motion visitors start
  with static content.
- **Style → Section layout**: background, responsive padding, and statistics gap.

`reference-stats.css` is generated by the CSS copy command above. `stats.js`
adapts the reference animation to Elementor's lifecycle. The avatar sheet and
Aspekta, Urbanist, and Momo Trust Sans fonts are bundled locally; font licenses
are included in `assets/fonts/`.

## Approach widget

Place **Jednotka Approach** after Stats & Testimonials in a full-width container
with zero padding and gap. Keep the parent container's overflow visible so the
reference's sticky scrolling works. The original paragraph, gray window, sticky
layout, responsive spacing, and letter-by-letter color wave are copied from the
example. The default section is deliberately taller than one screen to give the
wave room to progress as visitors scroll.

- Click the paragraph to edit it directly. **Style → Text and wave colors** sets
  its font, responsive size, and colors before, during, and after the reveal.
- **Content → Media window** keeps the original gray placeholder or replaces it
  with an image or uploaded video. Videos support a poster and a play/pause
  control, loop muted, and start paused in the editor and for reduced motion.
- **Style → Section layout** controls the background, scroll height, side padding,
  text/media gap, media corners, background, and image/video fit.
- In the editor the text remains unsplit and fully visible for inline editing.
  Use **Preview Changes** to see the wave and sticky scrolling. Turning off
  **Scroll color wave**, or using reduced motion, shows a static section without
  the extra scrolling distance.

The CSS copy command also regenerates `reference-approach.css`. `approach.js`
uses the calculations from `examples/js/gradient-wave-text.js`, scoped to each
widget, with Elementor initialization and cleanup.

## Results widget

Place **Jednotka Results** after Approach in a full-width container with zero
padding and gap. It copies the reference's two-column project layout, heading,
five project cards, image artwork, glass badges, and scroll-driven blur/scale
reveal. On mobile, the columns stack in the original order.

- Click the heading, any project name, description, or badge label to edit it.
  Each text has separate color and typography controls, including responsive
  sizes. The heading supports line breaks.
- **Content → Projects**: add/remove/reorder projects, choose their left or right
  column, and set their links (including new-tab and nofollow options). Project
  links do not navigate while editing text in Elementor.
- Each project image can be replaced with another image or an uploaded video
  with a poster. Badge icons can use the original SVG, an image, a video, or no
  icon. Badges can also be hidden. Set meaningful alternative text when needed.
- Each project has media fit, corner radius, background, badge background, and
  reference icon color controls. The hover/focus video button controls that
  project's main video and badge video together. Videos loop muted and start
  paused in the editor or for visitors who prefer reduced motion.
- **Content → Heading → Scroll reveal** toggles the original animation. Cards
  stay fully visible in the editor; use **Preview Changes** to see the reveal.
- **Style → Section layout** controls background, responsive padding, column
  gaps, and gaps between projects.

The CSS copy command regenerates `reference-results.css`; `results.js` retains
the original `examples/js/project-reveal.js` calculations with scoped handlers
and cleanup. Images and fonts are bundled independently of `examples/`.

## Services widget

Place **Jednotka Services** after Results in a full-width container with zero
padding and gap. It copies the reference heading, introduction, six service rows,
icon badges, responsive layout, blur/scale scroll reveal, and moving arrows.

- Click the heading, introduction, service names, or descriptions to edit them.
  Each text has its own color, font, and responsive size controls.
- **Content → Services**: add/remove/reorder rows, update their links, replace
  service icons or arrows with images/videos, and adjust icon size, badge
  backgrounds, and arrow colors. Videos support posters and loop muted.
- The video button appears on hover or keyboard focus and controls both videos
  in its service row. It sits outside the service link. Videos start paused in
  the editor and for visitors who prefer reduced motion.
- **Content → Heading and introduction**: toggle the scroll reveal and set the
  section anchor. The default `sluzby` supports the navigation's `#sluzby` link.
  Set each service's destination URL to its intended WordPress page.
- **Style → Section layout** controls background, padding, space between rows,
  and space below the introduction. Rows remain visible and links do not
  navigate while editing; use **Preview Changes** to see the scroll animation.

The CSS copy command regenerates `reference-services.css`; `services.js` uses
the reference `examples/js/services-reveal.js` calculations with Elementor
initialization and cleanup. Fonts and all six default icons are bundled locally.

## Adding widgets

Use the namespace `Jednotka\Elementor\Widgets`, unique widget names prefixed with
`jednotka-`, and the `jednotka` category. Import and register each widget in
`Plugin::register_widgets()` in `includes/class-plugin.php`.

Use Elementor Free controls and APIs. Escape rendered content with WordPress
helpers such as `esc_html()`, `esc_attr()`, `esc_url()`, or `wp_kses_post()` as
appropriate. Register widget assets with WordPress and declare them through
`get_style_depends()` / `get_script_depends()` so Elementor loads them when needed.

Reference: https://developers.elementor.com/docs/managers/registering-widgets/

## JavaScript initialization checks

Run `node tests/frontend-init.test.cjs` from this plugin folder. These checks cover
scripts loading before and after Elementor initializes, partially initialized
globals, and repeated initialization events.

Run `node tests/stats-motion.test.cjs` to check count-up, scroll reveal, pause,
editor/reduced-motion behavior, and cleanup when Elementor removes a widget.

Run `node tests/approach-motion.test.cjs` to compare the wave with the reference
at multiple scroll positions and check editing, custom colors, reduced motion,
video controls, and cleanup.

Run `node tests/results-motion.test.cjs` to compare the project reveal against
the reference and check editor behavior, video controls, reduced motion,
keyboard focus, and cleanup.

Run `node tests/services-motion.test.cjs` to compare service reveal and arrow
offsets against the reference and check editing, video controls, reduced motion,
keyboard focus, and cleanup. Visual comparisons should include Elementor's
frontend CSS and widget wrappers to catch styles that override reference rules.
