<?php
/** Editable team introduction and looping portrait track from temp/team. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Team_Widget extends Widget_Base {
	public function get_name() { return 'jednotka-team'; }
	public function get_title() { return esc_html__( 'Jednotka Team', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-person'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'team', 'people', 'carousel', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-team' ); }
	public function get_script_depends() { return array( 'jednotka-team' ); }

	private function text_controls( $stack, $key, $label, $selector, $default = '', $multiline = false ) {
		$stack->add_control( $key, array( 'label' => $label, 'type' => $multiline ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $default ) );
		$stack->add_control( $key . '_color', array( 'label' => $label . ' color', 'type' => Controls_Manager::COLOR, 'selectors' => array( $selector => 'color: {{VALUE}};' ) ) );
		$stack->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_typography', 'selector' => $selector ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'intro_section', array( 'label' => 'Introduction' ) );
		$this->text_controls( $this, 'heading', 'Heading', '{{WRAPPER}} .team__title', 'Meet our awesome team', true );
		$this->text_controls( $this, 'description', 'Description', '{{WRAPPER}} .team__text', 'Strategists, designers, writers and developers who pull in one direction. Small enough to stay close to every project, experienced enough to make each one count.', true );
		$this->end_controls_section();

		$this->start_controls_section( 'members_section', array( 'label' => 'Team members' ) );
		$member = new Repeater();
		$this->text_controls( $member, 'name', 'Name', '{{WRAPPER}} {{CURRENT_ITEM}} .team-card__name' );
		$this->text_controls( $member, 'role', 'Role', '{{WRAPPER}} {{CURRENT_ITEM}} .team-card__role' );
		$member->add_control( 'photo', array( 'label' => 'Photo', 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => JEDNOTKA_ELEMENTOR_URL . 'assets/images/team/placeholder.jpg' ) ) );
		$member->add_control( 'alt', array( 'label' => 'Photo alternative text', 'type' => Controls_Manager::TEXT, 'description' => 'Leave empty to use the member’s name.' ) );
		$member->add_control( 'fit', array( 'label' => 'Photo fit', 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => 'Fill card', 'contain' => 'Show complete photo' ), 'selectors' => array( '{{WRAPPER}} {{CURRENT_ITEM}} .team-card__image' => 'object-fit: {{VALUE}};' ) ) );
		$this->add_control( 'members', array( 'label' => 'Members', 'type' => Controls_Manager::REPEATER, 'fields' => $member->get_controls(), 'title_field' => '{{{ name }}}', 'prevent_empty' => false, 'default' => array(
			array( 'name' => 'Erik Beňuš', 'role' => 'CEO' ),
			array( 'name' => 'Petra Novotná', 'role' => 'Creative Director' ),
			array( 'name' => 'Katarína Malá', 'role' => 'Marketing Lead' ),
			array( 'name' => 'Martin Kováč', 'role' => 'Art Director' ),
			array( 'name' => 'Lucia Horváthová', 'role' => 'Copywriter' ),
		) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'motion_section', array( 'label' => 'Scrolling' ) );
		$this->add_control( 'auto_scroll', array( 'label' => 'Automatic scrolling', 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'speed', array( 'label' => 'Speed (pixels / second)', 'type' => Controls_Manager::NUMBER, 'default' => 45, 'min' => 5, 'max' => 200 ) );
		$this->add_control( 'stage_label', array( 'label' => 'Carousel accessible name', 'type' => Controls_Manager::TEXT, 'default' => 'Náš tím. Potiahnite myšou alebo použite šípky na klávesnici.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'layout_section', array( 'label' => 'Section and cards', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'background', array( 'label' => 'Background', 'type' => Controls_Manager::COLOR, 'default' => '#0F0F0F', 'selectors' => array( '{{WRAPPER}} .jn-team' => 'background: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .team__layout' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_height', array( 'label' => 'Card height', 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 180, 'max' => 1000 ), 'vh' => array( 'min' => 20, 'max' => 90 ) ), 'selectors' => array( '{{WRAPPER}} .team-card' => 'height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_gap', array( 'label' => 'Card gap', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .team-card' => 'margin-right: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_radius', array( 'label' => 'Card corners', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'selectors' => array( '{{WRAPPER}} .team-card' => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key, $class ) {
		$this->add_inline_editing_attributes( $key, 'none' );
		$this->add_render_attribute( $key, 'class', $class );
		$this->print_render_attribute_string( $key );
	}

	private function render_members( $members, $clone = false ) {
		foreach ( $members as $index => $member ) {
			$role_key = $this->get_repeater_setting_key( 'role', 'members', $index );
			$name_key = $this->get_repeater_setting_key( 'name', 'members', $index );
			?>
			<article class="team-card elementor-repeater-item-<?php echo esc_attr( $member['_id'] ); ?>">
				<?php if ( ! empty( $member['photo']['url'] ) ) : ?><img class="team-card__image" src="<?php echo esc_url( $member['photo']['url'] ); ?>" alt="<?php echo esc_attr( $clone ? '' : ( $member['alt'] ?: $member['name'] ) ); ?>" draggable="false" loading="lazy" decoding="async" /><?php endif; ?>
				<span class="team-card__shade" aria-hidden="true"></span>
				<div class="team-card__info">
					<h3 <?php if ( $clone ) { echo 'class="team-card__role"'; } else { $this->inline_text( $role_key, 'team-card__role' ); } ?>><?php echo esc_html( $member['role'] ); ?></h3>
					<p <?php if ( $clone ) { echo 'class="team-card__name"'; } else { $this->inline_text( $name_key, 'team-card__name' ); } ?>><?php echo esc_html( $member['name'] ); ?></p>
				</div>
			</article>
			<?php
		}
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="team jn-team" aria-labelledby="team-title-<?php echo esc_attr( $this->get_id() ); ?>" data-auto="<?php echo esc_attr( $s['auto_scroll'] ); ?>" data-speed="<?php echo esc_attr( max( 5, min( 200, (float) $s['speed'] ) ) ); ?>">
			<div class="team__layout">
				<div class="team__intro">
					<h2 id="team-title-<?php echo esc_attr( $this->get_id() ); ?>" <?php $this->inline_text( 'heading', 'team__title' ); ?>><?php echo nl2br( esc_html( $s['heading'] ) ); ?></h2>
					<p <?php $this->inline_text( 'description', 'team__text' ); ?>><?php echo esc_html( $s['description'] ); ?></p>
				</div>
				<div class="team__stage" role="region" tabindex="0" aria-label="<?php echo esc_attr( $s['stage_label'] ); ?>">
					<div class="team__track">
						<div class="team__group"><?php $this->render_members( $s['members'] ); ?></div>
						<div class="team__group" aria-hidden="true" inert><?php $this->render_members( $s['members'], true ); ?></div>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
