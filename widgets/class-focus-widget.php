<?php
/** Scroll statement with floating service badges. Shares the Our Story wave reveal. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Focus_Widget extends Widget_Base {
	// Left, top, mobile left, mobile top, rotation, and the scroll progress where each badge appears.
	const SLOTS = array(
		array( 8, 14, 6, 9, -4, .1 ),
		array( 44, 10, 56, 7, 2, .26 ),
		array( 77, 16, 30, 17, 3, .42 ),
		array( 13, 77, 10, 81, 3, .58 ),
		array( 72, 74, 56, 86, -3, .74 ),
	);

	public function get_name() { return 'jednotka-focus'; }
	public function get_title() { return esc_html__( 'Jednotka Focus', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-animation-text'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'focus', 'statement', 'scroll', 'badges', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-focus' ); }
	public function get_script_depends() { return array( 'jednotka-our-story' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Statement' ) );
		$this->add_control( 'text', array( 'label' => 'Text', 'type' => Controls_Manager::TEXTAREA, 'rows' => 4, 'default' => "Viete, čo vám reálne prináša zákazníkov?\nPomôžeme vám zorientovať sa v\u{00A0}marketingu a\u{00A0}zistiť, čo funguje.", 'description' => 'Press Enter to start a new line.' ) );
		$this->add_control( 'section_label', array( 'label' => 'Section accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Čo vám prináša zákazníkov' ) );
		$this->add_control( 'animate', array( 'label' => 'Scroll animation', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => 'Keep the parent Elementor container overflow visible.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'badges_section', array( 'label' => 'Badges' ) );
		$badge = new Repeater();
		$badge->add_control( 'label', array( 'label' => 'Text', 'type' => Controls_Manager::TEXT, 'default' => 'Služba' ) );
		$badge->add_control( 'icon', array( 'label' => 'Icon', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/hero/web.png' ) ) );
		$defaults = array();
		foreach ( array( 'Web' => 'web.png', 'E-shopy' => 'services-eshop.svg', 'Emaily' => 'email.png', 'Grafika' => 'grafika.png', 'Reklamy' => 'ppc.png' ) as $label => $icon ) {
			$defaults[] = array( 'label' => $label, 'icon' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/hero/' . $icon ) );
		}
		$this->add_control( 'badges', array( 'label' => 'Badges', 'type' => Controls_Manager::REPEATER, 'fields' => $badge->get_controls(), 'default' => $defaults, 'title_field' => '{{{ label }}}', 'prevent_empty' => false, 'max_items' => count( self::SLOTS ), 'description' => 'Up to five badges. Their order sets their position.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$editing = \Elementor\Plugin::$instance->editor->is_edit_mode();
		$this->add_inline_editing_attributes( 'text', 'none' );
		$this->add_render_attribute( 'text', 'class', 'jn-our-story__text jn-focus__text' );
		?>
		<section class="jn-our-story jn-focus" aria-label="<?php echo esc_attr( $s['section_label'] ); ?>" data-animate="<?php echo esc_attr( $s['animate'] ); ?>" data-editor="<?php echo $editing ? 'yes' : 'no'; ?>" data-static="<?php echo $editing ? 'yes' : 'no'; ?>" data-breaks="yes" data-final="#FFFFFF" data-muted="#585858" data-highlight="#8A5CFF">
			<div class="jn-our-story__sticky">
				<div class="jn-focus__badges" aria-hidden="true">
					<?php foreach ( array_slice( $s['badges'], 0, count( self::SLOTS ) ) as $index => $badge ) :
						$slot = self::SLOTS[ $index ];
						$style = sprintf( '--x: %1$d%%; --y: %2$d%%; --mx: %3$d%%; --my: %4$d%%; --r: %5$ddeg; --delay: %6$.1fs;', $slot[0], $slot[1], $slot[2], $slot[3], $slot[4], $index * -1.3 );
						?>
						<span class="jn-focus__badge" style="<?php echo esc_attr( $style ); ?>" data-start="<?php echo esc_attr( $slot[5] ); ?>" data-end="1" data-stay="yes"><span class="jn-focus__badge-inner">
							<?php if ( ! empty( $badge['icon']['url'] ) ) : ?><img src="<?php echo esc_url( $badge['icon']['url'] ); ?>" alt="" /><?php endif; ?>
							<?php echo esc_html( $badge['label'] ); ?>
						</span></span>
					<?php endforeach; ?>
				</div>
				<p <?php $this->print_render_attribute_string( 'text' ); ?>><?php echo esc_html( $s['text'] ); ?></p>
			</div>
		</section>
		<?php
	}
}
