<?php
/** Career perks and searchable, expandable positions adapted from temp/jobs. */
namespace Jednotka\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

class Career_Jobs_Widget extends Widget_Base {
	const EMAIL = 'info@jednotka.studio';

	public function get_name() { return 'jednotka-career-jobs'; }
	public function get_title() { return esc_html__( 'Jednotka Career Jobs', 'jednotka-elementor' ); }
	public function get_icon() { return 'eicon-post-list'; }
	public function get_categories() { return array( 'jednotka' ); }
	public function get_keywords() { return array( 'career', 'jobs', 'kariera', 'positions', 'search', 'perks', 'jednotka' ); }
	public function get_style_depends() { return array( 'jednotka-career-jobs' ); }
	public function get_script_depends() { return array( 'jednotka-career-jobs' ); }

	/** Inline icons for the perk cards, keyed by the select control's values. */
	private static function icons() {
		return array(
			'globe'     => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3Z"/>',
			'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
			'team'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.6-3.6 3.2-5.5 6.5-5.5s5.9 1.9 6.5 5.5"/><path d="M16 4.8a3.5 3.5 0 0 1 0 6.4M18 14.8c1.9.7 3.1 2.4 3.5 5.2"/>',
			'document'  => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
			'rocket'    => '<path d="M5 15c-1.5 1.5-2 5-2 5s3.5-.5 5-2"/><path d="M9 15 6.5 12.5C8 7 12 3.5 20.5 3.5 20.5 12 17 16 11.5 17.5Z"/><circle cx="14.5" cy="9.5" r="1.8"/>',
			'chart'     => '<path d="M4 20V4M4 20h16"/><path d="m7.5 15 4-4 3 3 5-6"/>',
		);
	}

	private static function default_perks() {
		return array(
			array( 'icon' => 'globe', 'perk_title' => '100 % remote', 'perk_text' => 'Celá agentúra funguje na diaľku – pracuješ odkiaľkoľvek.' ),
			array( 'icon' => 'clock', 'perk_title' => 'Vlastný time-management', 'perk_text' => 'Svoj čas si plánuješ podľa seba, dôležité sú výsledky a termíny.' ),
			array( 'icon' => 'team', 'perk_title' => 'Senior podpora od tímu', 'perk_text' => 'Programátori, kreatívci, PPC, Account aj Sales – vždy máš za kým ísť.' ),
			array( 'icon' => 'document', 'perk_title' => 'Forma spolupráce', 'perk_text' => 'Živnosť / s.r.o. (fakturácia) alebo dohoda (brigáda).' ),
		);
	}

	private static function apply_link( $position ) {
		return array( 'url' => 'mailto:' . self::EMAIL . '?subject=' . rawurlencode( 'Záujem o pozíciu: ' . $position ) );
	}

	private static function default_positions() {
		return array(
			array(
				'job_title'   => 'PPC Senior',
				'tags'        => 'Senior',
				'description' => 'Sme rastúca agentúra, ktorá neustále potrebuje senior odborníkov v oblasti PPC, predovšetkým Google & Meta. Skúsenosti s platformami TikTok, LinkedIn alebo ChatGPT sú výhodou.',
				'location'    => '100 % remote',
				'work_type'   => 'PPC · Google & Meta',
				'details'     => '<h4>Koho hľadáme?</h4><ul>'
					. '<li><strong>Senior PPC odborníka</strong> – senioritu nemeriame len počtom rokov praxe, ale aj reálnymi skúsenosťami s projektmi.</li>'
					. '<li>Si ochotný pracovať samostatne, si proaktívny a preberáš zodpovednosť za svoju prácu.</li>'
					. '<li>Si ochotný pravidelne komunikovať s tímom.</li>'
					. '<li>Zvládaš vlastný time-management a plnenie termínov.</li>'
					. '<li>Vezmeš si toľko klientov, koľko potrebuješ, chceš a zvládaš.</li></ul>',
				'link_text'   => 'Mám záujem',
				'link'        => self::apply_link( 'PPC Senior' ),
			),
			array(
				'job_title'   => 'Account manažér / manažérka',
				'tags'        => 'Vhodné pre absolventov',
				'description' => 'Ako agentúre nám počet klientov neustále rastie a tí potrebujú 100 % servis. Preto hľadáme možno práve teba.',
				'location'    => '100 % remote',
				'work_type'   => 'Klientský servis',
				'details'     => '<h4>Koho hľadáme?</h4><ul>'
					. '<li>V prvom rade si <strong>komunikatívny</strong> – nebojíš sa zdvihnúť telefón ani zavolať.</li>'
					. '<li>Využívaš rôzne formy komunikácie ako e-mail, hovory či WhatsApp.</li>'
					. '<li>Vo svojej práci vieš používať AI chatboty.</li>'
					. '<li>Máš predošlé znalosti z marketingu alebo ochotu sa to naučiť.</li>'
					. '<li>Zvládaš vlastný time-management.</li></ul>',
				'link_text'   => 'Mám záujem',
				'link'        => self::apply_link( 'Account manažér / manažérka' ),
			),
			array(
				'job_title'   => 'Sales Representative (Junior obchodník)',
				'tags'        => 'Junior, Zaškolenie',
				'description' => 'Hľadáme dravého človeka na štart obchodnej operatívy, ktorý sa nebojí nadväzovať nové kontakty a dohadovať obchodné príležitosti.',
				'location'    => '100 % remote',
				'work_type'   => 'B2B obchod',
				'details'     => '<h4>Zaškoľovací proces</h4>'
					. '<p>Nemusíš mať strach, že ťa hneď hodíme do vody. Prejdeš si dôkladným zaškoľovacím procesom, kde ti všetko ukážeme, naučíme ťa marketingové základy aj náš obchodný systém a prvými krokmi a stretnutiami prejdeme spoločne.</p>'
					. '<h4>Koho hľadáme?</h4><ul>'
					. '<li><strong>Dravca v oslovovaní</strong> – vyhľadávaš nové B2B príležitosti, oslovuješ firmy a dohaduješ úvodné stretnutia.</li>'
					. '<li><strong>Človeka s chuťou rásť</strong> – máš výborné vystupovanie, nevzdávaš sa pri prvom odmietnutí a chceš si zarobiť na základe svojho výkonu.</li></ul>'
					. '<h4>Odmeňovanie</h4><p>Výkonnostné:</p><ul>'
					. '<li>odmena za dohodnuté stretnutie,</li>'
					. '<li>provízie z uzatvorených obchodov,</li>'
					. '<li>retenčný bonus za klienta.</li></ul>',
				'link_text'   => 'Mám záujem',
				'link'        => self::apply_link( 'Sales Representative' ),
			),
			array(
				'job_title'   => 'Sales Executive (Senior obchodník)',
				'tags'        => 'Senior',
				'description' => 'Hľadáme skúseného obchodníka, ktorý vedie samostatné stretnutia, analyzuje potreby klientov, navrhuje riešenia a uzatvára finálne zmluvy.',
				'location'    => '100 % remote',
				'work_type'   => 'B2B obchod',
				'details'     => '<h4>Ako sa stať Sales Executive?</h4><ul>'
					. '<li><strong>Priamy vstup</strong> – ak už máš výrazné preukázateľné skúsenosti s B2B salesom v marketingu.</li>'
					. '<li><strong>Kariérny posun</strong> – zo Sales Representative po zaškoľovacej dobe (od 3 mesiacov) na základe tvojich výsledkov.</li></ul>'
					. '<p>Aj na tejto pozícii ťa na úvode kompletne zaškolíme do našich služieb, procesov a cenotvorby, aby si mal 100 % istotu pri komunikácii s klientmi.</p>'
					. '<h4>Koho hľadáme?</h4><ul>'
					. '<li><strong>Skúseného vyjednávača</strong> – vieš premeniť stretnutie na uzatvorenú spoluprácu.</li>'
					. '<li>Vieš odkomunikovať potreby klienta s tímom a predostrieť klientovi riešenie, ktoré bude chcieť využiť.</li>'
					. '<li><strong>Orientovaného v marketingu</strong> – rozumieš tomu, čo predávaš (PPC, web, e-mail marketing).</li></ul>'
					. '<h4>Odmeňovanie</h4><p>Výrazne výkonnostný model:</p><ul>'
					. '<li>podiel z uzatvorených projektov / retainerov okamžite po podpise zmluvy,</li>'
					. '<li>pasívny príjem za každého prineseného klienta počas jeho spolupráce s nami.</li></ul>',
				'link_text'   => 'Mám záujem',
				'link'        => self::apply_link( 'Sales Executive' ),
			),
		);
	}

	protected function register_controls() {
		$this->start_controls_section( 'perks_content', array( 'label' => 'Benefits' ) );
		$this->add_control( 'perks_heading', array( 'label' => 'Benefits heading', 'type' => Controls_Manager::TEXT, 'default' => 'Výhody práce u nás' ) );
		$perk = new Repeater();
		$perk->add_control( 'icon', array( 'label' => 'Icon', 'type' => Controls_Manager::SELECT, 'default' => 'globe', 'options' => array( 'globe' => 'Globe', 'clock' => 'Clock', 'team' => 'Team', 'document' => 'Document', 'rocket' => 'Rocket', 'chart' => 'Chart' ) ) );
		$perk->add_control( 'perk_title', array( 'label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => '' ) );
		$perk->add_control( 'perk_text', array( 'label' => 'Text', 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'perks', array( 'label' => 'Benefits', 'type' => Controls_Manager::REPEATER, 'fields' => $perk->get_controls(), 'default' => self::default_perks(), 'title_field' => '{{{ perk_title || "Benefit" }}}', 'prevent_empty' => false, 'description' => 'Remove all benefits to hide this block.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'jobs_content', array( 'label' => 'Heading and search' ) );
		foreach ( array( 'heading' => array( 'Heading', 'Otvorené pozície' ), 'search_label' => array( 'Search placeholder', 'Hľadať pozíciu' ), 'empty_message' => array( 'No positions message', 'Momentálne nemáme žiadne otvorené pozície.' ), 'no_results' => array( 'No search results message', 'Nič sme nenašli. Skúste iné slovo.' ) ) as $key => $field ) {
			$this->add_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::TEXT, 'default' => $field[1] ) );
		}
		$this->end_controls_section();

		$this->start_controls_section( 'positions_section', array( 'label' => 'Open positions' ) );
		$job = new Repeater();
		foreach ( array( 'job_title' => array( 'Position title', '' ), 'tags' => array( 'Labels (comma separated)', '' ), 'description' => array( 'Short description', '' ), 'location' => array( 'Location', '100 % remote' ), 'work_type' => array( 'Work type / area', '' ) ) as $key => $field ) {
			$job->add_control( $key, array( 'label' => $field[0], 'type' => 'description' === $key ? Controls_Manager::TEXTAREA : Controls_Manager::TEXT, 'default' => $field[1] ) );
		}
		$job->add_control( 'details', array( 'label' => 'Details (shown when expanded)', 'type' => Controls_Manager::WYSIWYG, 'default' => '' ) );
		$job->add_control( 'link_text', array( 'label' => 'Button text', 'type' => Controls_Manager::TEXT, 'default' => 'Mám záujem' ) );
		$job->add_control( 'link', array( 'label' => 'Button link', 'type' => Controls_Manager::URL, 'default' => array( 'url' => 'mailto:' . self::EMAIL ), 'description' => 'E-mail (mailto:) or an application page.' ) );
		// A new key so pages saved with the earlier, empty "jobs" list pick up these positions.
		$this->add_control( 'positions', array( 'label' => 'Positions', 'type' => Controls_Manager::REPEATER, 'fields' => $job->get_controls(), 'default' => self::default_positions(), 'title_field' => '{{{ job_title || "Position" }}}', 'prevent_empty' => false, 'description' => 'Search matches the title, labels, description, location, work type, and details.' ) );
		$this->add_control( 'apply_heading', array( 'label' => 'Apply box heading', 'type' => Controls_Manager::TEXT, 'default' => 'Zaujala ťa táto pozícia?', 'separator' => 'before' ) );
		$this->add_control( 'apply_text', array( 'label' => 'Apply box text', 'type' => Controls_Manager::TEXTAREA, 'default' => 'Pošli nám pár viet o sebe a svoje CV alebo LinkedIn. Ozveme sa ti.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'jobs_style', array( 'label' => 'Layout and style', 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array( 'background' => array( 'Background', '.jn-career-jobs', 'background', '#0f0f0f' ), 'heading_color' => array( 'Heading color', '.career-jobs-heading, {{WRAPPER}} .career-perks-heading', 'color', '' ), 'card_color' => array( 'Card background', '.career-job, {{WRAPPER}} .career-perk', 'background-color', '' ), 'accent_color' => array( 'Accent color', '.jn-career-jobs', '--career-accent', '' ), 'title_color' => array( 'Position title color', '.career-job h3', 'color', '' ), 'text_color' => array( 'Position text color', '.career-job-description, {{WRAPPER}} .career-job-meta, {{WRAPPER}} .career-job-details', 'color', '' ), 'empty_color' => array( 'Empty message color', '.career-jobs-empty', 'color', '' ) ) as $key => $field ) {
			$this->add_control( $key, array( 'label' => $field[0], 'type' => Controls_Manager::COLOR, 'default' => $field[3], 'selectors' => array( '{{WRAPPER}} ' . $field[1] => $field[2] . ': {{VALUE}};' ) ) );
		}
		foreach ( array( 'heading' => '.career-jobs-heading', 'title' => '.career-job h3', 'description' => '.career-job-description', 'details' => '.career-job-details', 'empty' => '.career-jobs-empty' ) as $key => $selector ) {
			$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => $key . '_font', 'selector' => '{{WRAPPER}} ' . $selector ) );
		}
		$this->add_responsive_control( 'max_width', array( 'label' => 'Content max width', 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 320, 'max' => 1800 ) ), 'selectors' => array( '{{WRAPPER}} .career-jobs-inner' => 'max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'padding', array( 'label' => 'Section padding', 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'selectors' => array( '{{WRAPPER}} .jn-career-jobs' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	private function inline_text( $key, $class = '', $mode = 'none' ) {
		$this->add_inline_editing_attributes( $key, $mode );
		if ( $class ) { $this->add_render_attribute( $key, 'class', $class ); }
		$this->print_render_attribute_string( $key );
	}

	private static function svg( $paths, $class = '' ) {
		return '<svg' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . ' viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $paths . '</svg>';
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$jobs    = is_array( $s['positions'] ?? null ) ? $s['positions'] : array();
		$perks   = is_array( $s['perks'] ?? null ) ? $s['perks'] : array();
		$icons   = self::icons();
		$id      = $this->get_id();
		$editing = class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode();
		?>
		<section class="jn-career-jobs" aria-labelledby="career-jobs-title-<?php echo esc_attr( $id ); ?>"><div class="career-jobs-inner">
			<?php if ( $perks ) : ?>
			<div class="career-perks">
				<?php if ( $s['perks_heading'] ) : ?><h2 <?php $this->inline_text( 'perks_heading', 'career-perks-heading' ); ?>><?php echo esc_html( $s['perks_heading'] ); ?></h2><?php endif; ?>
				<ul class="career-perks-list">
				<?php foreach ( $perks as $index => $perk ) : ?>
					<li class="career-perk elementor-repeater-item-<?php echo esc_attr( $perk['_id'] ?? '' ); ?>">
						<span class="career-perk-icon"><?php echo self::svg( $icons[ $perk['icon'] ?? '' ] ?? $icons['globe'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup. ?></span>
						<h3 <?php $this->inline_text( $this->get_repeater_setting_key( 'perk_title', 'perks', $index ) ); ?>><?php echo esc_html( $perk['perk_title'] ); ?></h3>
						<?php if ( $perk['perk_text'] ) : ?><p <?php $this->inline_text( $this->get_repeater_setting_key( 'perk_text', 'perks', $index ) ); ?>><?php echo esc_html( $perk['perk_text'] ); ?></p><?php endif; ?>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

			<div class="career-jobs-head">
				<h2 id="career-jobs-title-<?php echo esc_attr( $id ); ?>" class="career-jobs-heading"><span <?php $this->inline_text( 'heading' ); ?>><?php echo esc_html( $s['heading'] ); ?></span><?php if ( $jobs ) : ?><sup class="career-jobs-count"><?php echo esc_html( (string) count( $jobs ) ); ?></sup><?php endif; ?></h2>
				<label class="career-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4.5 4.5" /></svg><span class="career-search-sr"><?php echo esc_html( $s['search_label'] ); ?></span><input type="search" placeholder="<?php echo esc_attr( $s['search_label'] ); ?>" autocomplete="off" /></label>
			</div>

			<ul class="career-jobs-list">
			<?php
			foreach ( $jobs as $index => $job ) :
				$tags = array_filter( array_map( 'trim', explode( ',', (string) ( $job['tags'] ?? '' ) ) ) );
				$body = 'career-job-body-' . $id . '-' . $index;
				?>
				<li class="career-job elementor-repeater-item-<?php echo esc_attr( $job['_id'] ?? '' ); ?>">
					<details<?php echo $editing ? ' open' : ''; ?>>
						<summary aria-controls="<?php echo esc_attr( $body ); ?>">
							<div class="career-job-copy">
								<?php if ( $tags ) : ?><div class="career-job-tags"><?php foreach ( $tags as $tag ) : ?><span class="career-job-tag"><?php echo esc_html( $tag ); ?></span><?php endforeach; ?></div><?php endif; ?>
								<h3 <?php $this->inline_text( $this->get_repeater_setting_key( 'job_title', 'positions', $index ) ); ?>><?php echo esc_html( $job['job_title'] ); ?></h3>
								<?php if ( $job['description'] ) : ?><p <?php $this->inline_text( $this->get_repeater_setting_key( 'description', 'positions', $index ), 'career-job-description' ); ?>><?php echo nl2br( esc_html( $job['description'] ) ); ?></p><?php endif; ?>
								<?php if ( $job['location'] || $job['work_type'] ) : ?><div class="career-job-meta">
									<?php if ( $job['location'] ) : ?><span class="career-job-chip"><?php echo self::svg( '<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z"/><circle cx="12" cy="10" r="2.3"/>' ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup. ?><span <?php $this->inline_text( $this->get_repeater_setting_key( 'location', 'positions', $index ) ); ?>><?php echo esc_html( $job['location'] ); ?></span></span><?php endif; ?>
									<?php if ( $job['work_type'] ) : ?><span class="career-job-chip"><?php echo self::svg( '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7M3 12.5h18"/>' ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup. ?><span <?php $this->inline_text( $this->get_repeater_setting_key( 'work_type', 'positions', $index ) ); ?>><?php echo esc_html( $job['work_type'] ); ?></span></span><?php endif; ?>
								</div><?php endif; ?>
							</div>
							<span class="career-job-toggle"><span class="career-job-toggle-label" data-closed>Viac info</span><span class="career-job-toggle-label" data-open>Skryť</span><?php echo self::svg( '<path d="M12 5v14M5 12h14"/>' ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup. ?></span>
						</summary>
						<div class="career-job-body" id="<?php echo esc_attr( $body ); ?>">
							<?php if ( trim( wp_strip_all_tags( (string) $job['details'] ) ) ) : ?>
							<div <?php $this->inline_text( $this->get_repeater_setting_key( 'details', 'positions', $index ), 'career-job-details', 'advanced' ); ?>><?php echo wp_kses_post( $job['details'] ); ?></div>
							<?php endif; ?>
							<aside class="career-job-apply">
								<?php if ( $s['apply_heading'] ) : ?><p class="career-job-apply-heading"><?php echo esc_html( $s['apply_heading'] ); ?></p><?php endif; ?>
								<?php if ( $s['apply_text'] ) : ?><p class="career-job-apply-text"><?php echo esc_html( $s['apply_text'] ); ?></p><?php endif; ?>
								<?php if ( ! empty( $job['link']['url'] ) ) : $key = 'job_link_' . $index; $this->add_link_attributes( $key, $job['link'] ); $this->add_render_attribute( $key, 'class', 'career-job-link' ); ?>
								<a <?php $this->print_render_attribute_string( $key ); ?>><span <?php $this->inline_text( $this->get_repeater_setting_key( 'link_text', 'positions', $index ) ); ?>><?php echo esc_html( $job['link_text'] ); ?></span><?php echo self::svg( '<path d="M7 17 17 7M8 7h9v9"/>' ); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed markup. ?></a>
								<?php endif; ?>
							</aside>
						</div>
					</details>
				</li>
			<?php endforeach; ?>
			</ul>
			<p class="career-jobs-empty" role="status" <?php if ( $jobs ) { echo 'hidden'; } ?>><span data-jobs-empty <?php $this->inline_text( 'empty_message' ); ?>><?php echo esc_html( $s['empty_message'] ); ?></span><span data-jobs-no-results hidden <?php $this->inline_text( 'no_results' ); ?>><?php echo esc_html( $s['no_results'] ); ?></span></p>
		</div></section>
		<?php
	}
}
