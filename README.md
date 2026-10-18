# Jednotka Elementor

Starter WordPress plugin for this project's custom Elementor widgets. Requires
Elementor Free; Elementor Pro is not required.

## Getting started

1. Paste your reference folder into `examples/`.
2. Activate **Jednotka Elementor** in WordPress under **Plugins** when ready.
3. In the Elementor editor, find **Jednotka Header** under **Jednotka** and drag it
   into a container at the top of your page. Containers default to **Full Width**
   with **0px padding** while this plugin is active. Set the gap to zero so the
   widget controls its own spacing.

These container defaults apply sitewide and remain editable in Elementor.
Explicitly saved width and padding values take precedence; existing containers
that inherit Elementor's defaults can also pick up the new values. Padding on
smaller screens inherits the desktop value unless a responsive value is set.

Standard section backgrounds use **`#0F0F0F`**; neutral cards and input surfaces
use **`#1A1A1A`**. Purple gradients, colored accent cards, and glass effects are
preserved. Explicitly saved Elementor colors continue to override the defaults.

Purple CTA buttons keep their labels stationary in the Elementor editor,
including on hover and focus. The duplicate animated label is hidden so the
original remains available for inline editing. These shared overrides load
only in Elementor's preview canvas; public button animations are preserved.

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
- Set a poster when using videos. Videos loop muted automatically; the hero has no corner animation button. Badges float as whole
  pills and respond to pointer movement on desktop, as in the reference. Floating
  is visible in the editor and pauses while typing. Videos start paused in the
  editor; motion pauses for visitors who prefer reduced motion and while the tab is hidden.

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
- There is no animation control button. Reduced-motion visitors
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
  visible in the editor, with floating motion pausing while typing. There is no
  animation control button. Videos start paused in the editor; reduced-motion visitors start
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

**Jednotka Services** now includes the `temp/services` detail upgrade while
retaining its widget name, service repeater, existing text, icons/videos,
arrow media, colors, and typography controls. The list keeps the reference
blur/scale reveal. Its arrow opens a sliding detail pane with a heading,
benefit points, a continuously scrolling three-column image wall, and the
hero-style CTA. On mobile the wall sits below the copy.

- **Content → Services**: add/remove/reorder services; edit the original names,
  descriptions, links, icon/arrow media, colors, and sizing. Each service now
  has an arrow action, optional detail slug, detail heading, benefit lines,
  image gallery, and CTA text/link. Existing plugin gallery images fill empty
  galleries; replace them with your own service/project photos.
- **Arrow action → Auto** preserves existing custom destination URLs. Empty
  links and the original default `/sluzby/` destination open a detail. Choose
  **Open service detail** or **Follow service link** to override that behavior.
- **Content → Heading and introduction** retains the heading, intro, scroll
  reveal, and section anchor (`sluzby`). It also includes the back label and
  an editor detail-preview toggle with a service number. Click any service arrow in Elementor to preview its detail, then edit the
  heading, benefits (one per line), and button text directly on the canvas.
  The selected service stays open through widget refreshes; Back returns to
  the editable list. Service arrows with custom URLs preview their detail in
  the editor while retaining their normal destination on the published page.
  Use the editor-only **Edit bullet points** and **Edit images** buttons to
  open the matching service row in the sidebar. Clicking the image wall also
  opens its WordPress gallery picker. The sidebar fields are labeled
  **Detail — Bullet points (one per line)** and **Detail — Images**. Uploaded
  images resolve from their media-library attachment IDs as well as URLs.
  Link controls change the detail CTA destination.
- **Style → Section layout** retains the background, responsive padding,
  row spacing, and introduction spacing. Detail heading color/typography is
  editable per service. Video controls still support both row media videos;
  opening a detail pauses them and closing resumes only videos previously
  playing. Editor and reduced-motion views do not autoplay videos.

Details use widget-specific hashes such as `#jn-services-<widget-id>-weby`;
copy the URL after opening a service to link directly to it. The first Services
widget also accepts reference aliases (`#weby`, `#eshopy`, `#emaily`, `#reklamy`,
`#grafika`, `#branding`). Explicit slugs survive title edits. Back/Forward, the
back button, and Escape switch views; focus returns to the service arrow. Only
the active pane is interactive, and the viewport height follows its content.
The default CTA target is `#kontakt`; set the contact section's Advanced CSS ID
to `kontakt` or replace the CTA destination.

The existing reference stylesheet still supplies shared variables and list
compatibility. The updated styles and script are bundled in `assets`, with no
runtime dependency on `temp`. Reduced motion disables transitions and wall
scrolling; Elementor teardown restores the readable list and removes observers.

## Clients & Footer widget

**Jednotka Clients & Footer** combines a contact CTA with a responsive footer.
The purple gradient, display type, hero-style button, and floating project media
match the other Jednotka widgets. The footer stays in document flow as its
content grows; navigation columns stack on smaller screens.

- **Content → Call to action**: edit the heading, description, button
  label/link, and phone introduction/number/link. The button defaults to the
  contact page. Replace the demo phone number and its `tel:` link before use.
- **Content → Floating projects**: up to six images or uploaded videos with
  names, categories, optional project links, and responsive widths/corners.
  Replace the bundled demo image with your projects. Cards float continuously;
  idle sway and gentle image pan/zoom continue without hovering. Hover and
  keyboard focus lift the card surface without stopping that motion.
  **Project entrance animation** reveals the larger cards with a staggered spring
  and blur as they enter the viewport. **Mouse glow** controls a soft light following the mouse, without ripple trails.
  **Snap CTA to viewport** smoothly aligns the CTA after scrolling settles near
  its top, respecting reduced motion and editor mode.
  Four cards remain visible on phones to keep the CTA clear.
- Footer brand, navigation, contact, social links, and copyright remain editable.
  The optional newsletter is still unconnected and displays its configured
  notice rather than submitting email addresses.
- **Style → Section and footer layout**: gradient colors, CTA padding, section
  minimum height, and footer background/padding/corners. Existing saved content
  and links are retained when updating the plugin.

Reduced-motion preferences disable motion, and editor typing pauses drift.
Run `node tests/clients-footer-motion.test.cjs` for motion, reduced motion, editor,
newsletter, and cleanup checks. Redesign styles live in `clients-footer.css`,
so regenerating reference CSS does not overwrite them.

## Footer widget

**Jednotka Footer** is a separate footer-only widget in **Jednotka**, sharing
its footer markup and controls with **Jednotka Clients & Footer**. It includes
the logo (image or uploaded video), brand description, menu/contact/social
repeaters, copyright, and optional newsletter placeholder. It has no CTA,
floating projects, pointer effects, snapping, or reserved CTA height.

The responsive card keeps the existing footer design with a 1360px default
maximum width. Outer background defaults to `#0F0F0F`. Layout controls cover outer
and inner padding, card background, corners, and maximum width. Text supports
inline editing. The newsletter remains unconnected: clicking its arrow or
pressing Enter shows the editable notice without submitting or storing email.
Logo videos respect reduced motion and pause while editing or in hidden tabs.
The original Clients & Footer widget retains its CTA and footer.

## Showcase & Comparison widget

Each entry under **Content → Gallery items** has an **Image / Text** content
type. Text entries support inline editing, color, and typography and grow with
their content without a fixed height or image aspect ratio. Existing image
entries retain their images.

Turn off **Content → Before / after comparison → Show before / after section**
to display only the gallery and introduction. Existing widgets show both sections
by default.

**Jednotka Showcase & Comparison** recreates the sticky 40/60 introduction and
image gallery, then the before/after comparison from `temp/`. It uses the hero's
Urbanist display font, Aspekta body font, white text, and a near-black (`#0F0F0F`) background. The
reference's final CTA is omitted; place **Jednotka Clients & Footer** below it.
Gallery and comparison sections are centered with a maximum width of 1440px,
while the black widget background spans the available width.

- **Content → Gallery introduction**: heading, heading tag, description, and
  desktop sticky behavior. Keep the parent container overflow visible. On
  tablets and phones the introduction and gallery stack without sticky behavior.
- **Content → Gallery images**: add, remove, reorder, or replace images; set
  alternative text and image fit. The four reference images are bundled locally.
- **Content → Before / after comparison**: both images and their alternative
  text, heading/description, before/after labels, accessible slider name, resting
  position and intro sweep. Replace the demo images with your
  matching before/after work. Drag with a mouse or touch, or use arrow keys,
  Home, and End after focusing the slider. Before appears on the left and After
  on the right; the full arrow handle straddles the image edge at either extreme.
  The comparison fills the viewport height. **Snap comparison to viewport**
  smoothly aligns the section after scrolling settles near its top; it respects
  reduced-motion preferences and stays disabled in the editor.
- **Style → Theme and layout**: gallery background, comparison background/accent, image
  gap/corners, section padding, and comparison maximum width. Text controls also
  provide colors and typography settings.

The comparison slides over the pinned gallery and eases out its upper corners
as it reaches the viewport top. Its intro sweeps from 100% to the configured
position once visible, unless reduced motion, editor mode, or user interaction
disables it. No pagewide scroll-snap settings are applied. Assets do not depend
on `temp/`, and multiple widget instances operate independently.

Run `node tests/showcase-interaction.test.cjs` for drag, keyboard, intro,
resize/sticky geometry, reduced motion, multiple instances, and cleanup checks.

## About Hero widget

**Jednotka About Hero** recreates the simple `temp/about-hero` example: “Toto je,”
the Jednotka logo, and two glass badges (“Rôzne skilly” / “Spoločný cieľ”). Find it
under **Jednotka**. Heading/tag, logo/alternative text, badge labels, colors,
typography, logo width, background, and padding are editable. Badges float gently
by default; disable **Floating animation** for a static version. Reduced motion
disables floating, and editing text pauses it. It uses bundled fonts and logo,
with no runtime dependency on `temp/`.

## Team widget

**Jednotka Team** recreates `temp/team`: an introduction on the left and a
looping, draggable row of portrait cards on the right. Find it under **Jednotka**.
It uses local Urbanist/Aspekta fonts, a black background, and a centered 1440px
layout. On smaller screens, the introduction appears above the cards.

- **Introduction**: heading and description, with text colors and typography.
- **Team members**: add/remove/reorder members, replace photos, and edit names,
  roles, alternative text, image fit, colors, and typography. Demo portraits are
  bundled locally; replace them with your team photos.
- **Scrolling**: automatic scrolling, speed (default 45px/second), and accessible
  carousel name. Drag with mouse/touch, or focus the track and use arrow keys,
  Home, and End. Keyboard focus pauses automatic movement.
- **Section and cards**: background, padding, card height, gap, and corners.

Repeated cards are hidden from assistive technology. Extra copies cover the
track even with one member. Automatic movement stops offscreen, in hidden tabs,
in the editor, and for reduced-motion users; manual dragging remains available.
Run `node tests/team-motion.test.cjs` for motion, looping, drag, keyboard,
reduced-motion, and cleanup checks. Assets do not depend on `temp/`.

## Our Story widget

**Jednotka Our Story** recreates the scroll-driven statement and sequential
photos from `temp/approach`, separately from the existing Approach widget.
Find it under **Jednotka**. The statement stays centered in a sticky screen;
letters light up with a purple wave while four decorative photos fade and scale
in/out during consecutive scroll windows. Hover restores a photo's colors.

- **Our story**: statement, accessible section name, and scroll animation.
- **Story photos**: add/remove/reorder photos, replace the bundled demo images,
  and adjust their aspect ratios, positions, widths, and appearance windows.
  Reference photo files were not supplied; existing plugin demo images are used.
- **Statement and layout**: typography, wave/revealed/unrevealed colors, black
  background, responsive scroll height, sticky frame padding, and photo corners.

Keep the parent Elementor container overflow visible. The editor, disabled
animation, and reduced-motion views show a static readable statement without
the long scroll space. Photo positions are bounded by a centered 1440px frame;
all text is exposed once to screen readers. Assets do not depend on `temp/`.
Run `node tests/our-story-scroll.test.cjs` for scroll windows, text accessibility,
custom colors, static modes, batching, and lifecycle cleanup checks.

## Values widget

**Jednotka Values** recreates `temp/values`: a pinned introduction beside a stack
of purple cards, with scroll-driven entry/settling and mouse tilt/glow. Find it
under **Jednotka**. It uses bundled Urbanist/Aspekta fonts, a black background,
and a centered 1440px layout. The five supplied values are included as defaults.

- **Introduction**: heading/description, colors, and typography.
- **Values**: add/remove/reorder cards and edit titles, descriptions, typography,
  and individual backgrounds. Card numbers follow the item order.
- **Animation**: scroll animation, building/falling stack modes, and mouse tilt.
- **Section and card layout**: background, default card/glow colors, scroll
  distance per card, layout/card padding, and corners.

Keep parent container overflow visible. The editor, reduced motion, disabled
animation, and lists with fewer than two values show a static readable layout.
Animations stop offscreen or in hidden tabs, and mouse interpolation stops once
settled. No runtime dependency on `temp/` remains. Run
`node tests/values-scroll.test.cjs` for stack/fall progression, pointer easing,
static modes, batching, and cleanup checks.

## Projects widget

**Jednotka Projects** adapts `temp/projects` into an editable project gallery in
the **Jednotka** category. Six tilted columns loop in alternating directions,
with varied speeds and image zoom/caption effects on hover. The section uses a
near-black (`#0F0F0F`) background and the existing Urbanist and Aspekta fonts.

Edit each project's name, type, image, alternative text, link, aspect ratio,
and column in the Projects repeater. The original thirty sample entries use
existing plugin gallery photos and placeholders; replace them with project
images in Elementor. Section colors, typography, padding, gallery height,
card spacing, and corners are configurable, alongside automatic scrolling
and loop duration.

Columns scroll continuously, including during hover, focus, and page scrolling.
Mobile combines the same projects into three looping columns. Editor,
reduced-motion, and scrolling-disabled views show every original project in a
static grid. Duplicate strips are hidden from assistive technology and cannot
receive keyboard focus. Runtime assets do not depend on the `temp` folder.

## Contact widget

**Jednotka Contact** adapts `temp/contact` into a two-column contact section with
a glass form, purple glow on black, the site's fonts, and the hero's animated
button. Find it in the **Jednotka** Elementor category. Edit the introduction,
field labels and placeholders, service options (one per line), button, and note;
colors, typography, padding, and column spacing are configurable. The layout
stacks on smaller screens and the form becomes a single column on phones.

Submissions use WordPress AJAX and `wp_mail` to the administration email in
**Settings → General**. The visitor's email is used only as Reply-To. Configure
and verify your site's email delivery before launch; successful `wp_mail`
acceptance does not guarantee inbox delivery. No email is sent from the editor.
The form validates names/emails, verifies a nonce, includes a honeypot, and
limits submissions to one per minute per IP. Expired cached nonces produce a
refresh message. Errors retain the entered text; successful submissions reset
the form. The button locks while sending and a live status announces results.
Runtime assets do not depend on `temp`.

## Services Hero widget

**Jednotka Services Hero** adapts `temp/hero`, `hero-badges`, and `drag-scroll`
into a services-page introduction in the **Jednotka** category. It includes a
shared editable navbar, multiline heading, five glass badges with
floating/pointer movement, and a row of service cards extending below the
reference purple-to-white gradient onto a white background. It uses the
reference Urbanist, Aspekta, and Momo fonts, bundled service icons, and project
placeholder. The navbar includes editable logo/media, links, action button,
and a mobile menu; it can be hidden using Show navigation.

Click the heading, badge labels, and service card titles/descriptions to edit
text directly on the Elementor canvas. The navbar text also supports inline
editing. Edit the heading/tag, badge labels/icons/positions, and service card titles,
descriptions, images, alternative text, and links. Automatic scrolling and
speed, badge motion, colors, typography, card size, and corners are configurable.
Cards default to the reference placeholder link and arrow; replace their links
with real destinations. Removing a link removes the arrow and navigation.

The card row drags with mouse/touch, supports native horizontal scrolling and
ArrowLeft/ArrowRight/Home/End, and automatically bounces at its ends. Fractional
scroll progress avoids stalls at low speeds. Manual interaction briefly pauses
automatic scrolling; keyboard focus pauses it until focus leaves. Links remain
clickable after a normal click, while a drag suppresses its click. Editor and
reduced-motion modes disable automatic movement. Motion also stops offscreen
and in hidden tabs; Elementor teardown removes listeners and observers.
Runtime assets do not depend on `temp`.

## FAQ widget

**Jednotka FAQ** recreates `temp/faq` in the **Jednotka** category: a heading,
description and glass-arrow contact link beside four editable questions.
It uses the reference dark background, Urbanist/Aspekta/Momo typography, thin
dividers, and orange expand/collapse symbols. The content has a 1290px maximum
width and stacks into one column below 900px.

Add, remove, reorder, or edit questions and answers in the repeater, including
which answers initially open. Text supports Elementor inline editing; all
answers initially open in the editor. Colors, typography, padding, column gap,
and the contact label/link are editable. The default contact target is
`#kontakt`; set the contact section's **Advanced → CSS ID** to `kontakt`, or
change the link to your contact page.

Accordions use native `details`/`summary` with mouse and keyboard support and
open one answer at a time, closing the previous answer smoothly. Answers
animate open/closed, moving the following rows smoothly. **Keep section height
stable** reserves room for the tallest single answer, so the section does not
grow as answers open; disable it
for a compact section that grows naturally. Reduced motion uses instant
toggles, and the editor retains native controls for inline editing. Without
JavaScript the native accordions still work. Runtime assets do not depend on
the `temp` folder.

## How We Work widget

**Jednotka How We Work** adapts `temp/work` and `work-scroll` into an editable
process timeline in the **Jednotka** category. The four reference steps include
numbered purple nodes, a gradient connecting line, Momo step titles, and the
site's Urbanist/Aspekta typography on a dark background. Content is capped at
1290px and the timeline becomes a vertical stack below 900px.

Edit the heading and add/remove/reorder steps using the repeater; heading,
titles, and descriptions also support Elementor inline editing. Numbering
updates automatically. Colors, typography, section padding, and step spacing
are configurable. The line draws, nodes pop in, and text reveals with the
reference stagger when the timeline reaches 78% of the viewport. Scrolling
back above that threshold resets it for replay.

The animation switch, editor mode, reduced-motion preference, and JavaScript
fallback keep all content visible. Scroll updates use one queued animation
frame; teardown removes listeners and restores the static view. Runtime assets
do not depend on `temp`.

## Blog Featured widget

**Jednotka Blog Featured** recreates `temp/featured`: a Blog heading and
large lead-article card with a glass category badge, excerpt,
publication date, and a white arrow. Find it in **Jednotka**. The
near-black background is scoped to this widget; it does not change the page body.
Content defaults to 1240px wide and stacks below 980px.

**Editable article** is the default source. Edit the heading, article
text, category, date, image, icon, and destination. Text supports
Elementor inline editing; the card does not navigate in the editor. Colors,
typography, content width, heading section padding, image corners, and column
gap are configurable.

Choose **Choose a WordPress post** to select a specific published post using
a searchable title dropdown, or **Latest published WordPress post** to follow
the newest post automatically. Both use the post title, short description
(falling back to the excerpt/content), category, date, thumbnail, and permalink.
Edit those fields in WordPress Posts. Private, draft, password-protected,
deleted, and non-post selections are not displayed. The dropdown loads only
in the editor/admin and includes every published, unprotected post. The image control supplies a fallback if the post has no
thumbnail; an empty blog shows a clear empty state. Runtime assets do not
depend on `temp` and no widget JavaScript is needed.

## Posts widget

**Jednotka Posts** adapts `temp/posts` into a live WordPress post archive in the
**Jednotka** category. It shows published, non-password-protected posts, newest
first, with a sticky search toolbar, category filters, image cards, dates, and
numbered pagination. The layout has two columns on desktop and one on mobile.

Create articles under **WordPress → Posts**. The plugin provisions six service
categories: Web, E-shopy, Email, PPC, Grafika, and Branding. Existing matching
categories (including aliases such as Reklamy) keep their IDs and assignments.
The post editor's **Kategórie služieb** sidebar box offers these six choices,
with images from `assets/images/services/`; both block and classic editors use
this picker. Native category creation, renaming, and deletion are disabled,
new category insertion is rejected, and REST post updates accept only the six
service category IDs. Tags are unaffected. Existing unrelated categories are
preserved but are not offered by the picker or the widget. Uncategorized stays
hidden. All six filters remain visible, including categories with no posts.

Use the sidebar **Krátky popis** textarea for the text below each title in the
Posts widget. Its plain text is displayed in full and saved as the protected
`_jednotka_short_description` post meta field. Leaving it empty falls back to
the existing excerpt or the first 25 words of the article. Saves check the
nonce and editing permission and skip autosaves and revisions. The field is
also registered for authenticated REST editing. Add featured images through
the normal WordPress image control. Without posts, the widget shows its
editable empty-state message.

The default is **6 posts per page** (configurable from 1 to 24). For 100 posts,
that produces 17 pages; only the current page's cards are rendered. Search and
category filters run together on the server across the full archive, reset to
page one, and keep pagination compact. Images load lazily. Search waits 400ms
after typing, cancels outdated requests, and updates results without a full page
reload. Numbered links and the search form also work without JavaScript. URLs
retain the search, category, and page; browser back/forward restores the archive.

Elementor controls include page size, search/filter/empty-state labels, fallback
image, background, typography, content width, bottom padding, and sticky offset
(for a fixed header). Post text is managed in WordPress Posts. The featured
widget remains separate and is not filtered by this archive. Assets are scoped
to the widget and do not depend on `temp`.

## Gallery widget

**Jednotka Gallery** recreates the one-to-nine-image mosaics in `temp/gallery`.
Find it in **Jednotka**, edit the inline heading, and choose/reorder images in
the Elementor gallery control. Only the first nine valid images are displayed.
Image counts use the reference 12-column layouts; phones use two columns with
an odd first image spanning the full width. No captions or filenames are shown.

The lightbox opens full-size Media Library images with their alternative text;
the grid loads large thumbnails lazily. It supports previous/next buttons,
arrow keys, Escape, backdrop closing, and animated transitions to/from the
thumbnail. A native modal dialog contains keyboard focus; closing restores the
clicked link and the previous scroll setting. Reduced motion skips animation.
Opening a different gallery closes the current one; Elementor re-rendering
cleans up its dialog and event handlers. In the editor, image clicks stay in
the editor. Disable the lightbox to use normal image links; links also work
without JavaScript. The single-image gallery hides navigation buttons.

Style controls cover background, heading color/typography, section padding,
content width, grid row height/gap, heading spacing, and image corners.
Local showcase images provide initial placeholders. Runtime assets do not
depend on `temp`.

## Career Hero widget

**Jednotka Career Hero** adapts the career-page reference in `temp/hero`: a
rounded photograph card, bottom dark gradient, and large **Kariéra** title.
Find it in **Jednotka**. The default maximum width is 1290px; smaller screens
use the reference's 320px minimum height and 22px corners. Long headings can
wrap and increase the card height.

Edit the title inline or in the panel, choose an H1/H2/H3 tag and image, and
set alternative text. Responsive controls cover width, minimum height, corners,
image crop position, outer spacing, and title padding. Typography, title color,
section background, and bottom shade opacity are editable. The supplied
reference does not include its `assets/career/hero.jpg` photo, so the initial
image uses the existing local team placeholder; replace it in the image control.
This widget needs no JavaScript and has no runtime dependency on `temp`.

## Career Jobs widget

**Jednotka Career Jobs** adapts `temp/jobs`: an **Otvorené pozície** heading,
rounded search input, and an honest empty state. It starts with no positions.
Find it in **Jednotka**, then add positions using the **Open positions** repeater.
Each position has a title, short description, location, work type, and optional
application/details link. Text supports inline editing. The list has a 1290px
default maximum width and stacks its heading, search, and position actions on
small screens. Colors, typography, width, and section padding are editable.

Search matches titles, descriptions, locations, and work types, ignoring case
and accents. The empty-archive and no-search-results messages are distinct and
editable. Each widget filters only its own list; removing a widget cleans up
its listeners. Search filtering is disabled in the Elementor editor so all
positions remain available for inline editing; links do not navigate there.
Without JavaScript, all positions remain visible. This is a manually managed
list, not a jobs post type or application submission system. Assets are bundled
locally and do not depend on `temp`.

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
