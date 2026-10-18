<?php
/** Standalone posts archive checks with a 100-post query fixture. */
namespace Elementor {
 class Controls_Manager { const TEXT='text',TEXTAREA='textarea',COLOR='color',RAW_HTML='raw',GALLERY='gallery',MEDIA='media',SELECT='select',REPEATER='repeater',SWITCHER='switcher',NUMBER='number',URL='url',TAB_STYLE='style',SLIDER='slider',DIMENSIONS='dimensions'; }
 class Group_Control_Typography { static function get_type(){ return 'typography'; } }
 class Plugin { public static $instance; }
 class EditorStub { public $editing=false; function is_edit_mode(){return $this->editing;} }
 class Repeater {
  protected $controls=[]; function add_control($key,$args){$this->controls[$key]=$args;} function get_controls(){return $this->controls;} function add_responsive_control($key,$args){$this->add_control($key,$args);} function add_group_control($type,$args){}
 }
 class Widget_Base extends Repeater {

  function __construct(){ $this->register_controls(); }
  function start_controls_section($id,$args){} function end_controls_section(){}
  function add_control($key,$args){$this->controls[$key]=$args;}
  function add_responsive_control($key,$args){$this->add_control($key,$args);}
  function add_group_control($type,$args){}
  public $overrides=[];
  function get_settings_for_display(){ $s=[]; foreach($this->controls as $k=>$a){$s[$k]=$a['default']??'';if(($a['type']??'')==='repeater'){$d=[];foreach($a['fields'] as $key=>$f)$d[$key]=$f['default']??'';foreach($s[$k] as $i=>&$item)$item=array_merge($d,$item,['_id'=>'item'.$i]);unset($item);}} return array_replace($s,$this->overrides); }
  private $attrs=[];
  function update_control($key,$args){$this->controls[$key]=array_replace($this->controls[$key],$args);}
  function add_link_attributes($key,$args){if(!empty($args['url']))$this->add_render_attribute($key,'href',$args['url']);}
  function add_render_attribute($key,$name,$value=null){if(is_array($name)){foreach($name as $k=>$v)$this->add_render_attribute($key,$k,$v);return;}$this->attrs[$key][$name][]=$value;}
  function print_render_attribute_string($key){foreach($this->attrs[$key]??[] as $name=>$values)echo $name.'="'.\esc_attr(implode(' ',$values)).'" ';}
  function add_inline_editing_attributes($key,$mode){$this->add_render_attribute($key,'data-elementor-setting-key',$key);$this->add_render_attribute($key,'class','elementor-inline-editing');}
  function get_repeater_setting_key($field,$key,$index){return "$key.$index.$field";}
  function get_id(){return 'preview';}
  function output(){ob_start();$this->render();return ob_get_clean();}
 }
}
namespace {
 define('ABSPATH',__DIR__);define('JEDNOTKA_ELEMENTOR_URL','/');
 function esc_html($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');} function esc_attr($v){return esc_html($v);} function esc_url($v){return esc_html($v);} function esc_html__($v,$d){return $v;}
 function home_url($path){return $path;} function esc_attr__($s,$d){return esc_attr($s);}
 function admin_url($path){return '/'.$path;} function wp_create_nonce($action){return 'preview-only';}
 function sanitize_title($s){$v=iconv('UTF-8','ASCII//TRANSLIT',$s);return trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/','-',$v)),'-');}
 function wp_get_attachment_image_url($id,$size){return 'https://example.com/media-'.$id.'.jpg';}
 function untrailingslashit($s){return rtrim($s,'/');}
 function get_posts($args){$GLOBALS['query']=$args;return $GLOBALS['posts']??[];}
 function get_the_title($post){return $post->post_title;}
 function strip_shortcodes($text){return preg_replace('/\[[^\]]+\]/','',$text);}
 function wp_strip_all_tags($text){return strip_tags($text);}
 function wp_trim_words($text,$limit,$suffix){$words=preg_split('/\s+/',trim($text));return implode(' ',array_slice($words,0,$limit)).(count($words)>$limit?$suffix:'');}
 function get_the_category($id){return $GLOBALS['post_categories']??[(object)['name'=>'Web','slug'=>'web','term_id'=>1]];}
 function get_the_date($format,$post){return '17. október 2026';}
 function get_permalink($post){return 'https://example.com/post/'.$post->ID;}
 function get_the_post_thumbnail_url($post,$size){return $post->thumbnail??false;}
 \Elementor\Plugin::$instance=(object)['editor'=>new \Elementor\EditorStub];

 function sanitize_text_field($v){return trim(strip_tags($v));}
 function wp_unslash($v){return stripslashes($v);}
 function absint($v){return abs((int)$v);}
 function get_terms($args){$GLOBALS['terms_query']=$args;return $GLOBALS['terms']??[(object)['term_id'=>1,'name'=>'Web','slug'=>'web'],(object)['term_id'=>2,'name'=>'Reklamy','slug'=>'reklamy']];}
 function is_wp_error($v){return false;}
 function remove_query_arg($keys){return '/blog/';}
 function add_query_arg($args,$base){return $base.'?'.http_build_query($args);}
 function wp_kses_post($v){return $v;}
 function paginate_links($args){$GLOBALS['pagination']=$args;return '<a class="page-numbers" href="'.str_replace('%#%',2,$args['base']).'">2</a>';}
 class WP_Query {
  public $posts,$found_posts,$max_num_pages;
  function __construct($args){
   $GLOBALS['queries'][]=$args;
   $all=array_values(array_filter($GLOBALS['fixture'],function($p)use($args){
    return (!isset($args['post__in'])||in_array($p->ID,$args['post__in'],true))&&$p->post_status==='publish'&&!$p->post_password&&(!$args['s']||stripos($p->post_title.' '.$p->post_excerpt.' '.$p->post_content,$args['s'])!==false)&&(!isset($args['cat'])||$args['cat']===$p->category);
   }));
   $this->found_posts=count($all);$this->max_num_pages=(int)ceil(count($all)/$args['posts_per_page']);
   $this->posts=array_slice($all,($args['paged']-1)*$args['posts_per_page'],$args['posts_per_page']);
  }
 }
 function get_post_meta($id,$key,$single){return $GLOBALS['descriptions'][$id]??'';}
 require dirname(__DIR__).'/includes/class-post-settings.php';
 require dirname(__DIR__).'/widgets/class-posts-widget.php';
 function check($yes,$message){if(!$yes)throw new \RuntimeException($message);}
 $GLOBALS['fixture']=[];
 for($i=100;$i>=1;$i--){$GLOBALS['fixture'][]=(object)['ID'=>$i,'post_status'=>'publish','post_password'=>'','post_title'=>'Article '.$i,'post_excerpt'=>'Post summary','post_content'=>'Full article content','category'=>$i%2?1:2,'thumbnail'=>false];}
 $draft=clone $GLOBALS['fixture'][0];$draft->post_status='draft';$GLOBALS['fixture'][]=$draft;
 $protected=clone $GLOBALS['fixture'][0];$protected->post_password='secret';$GLOBALS['fixture'][]=$protected;
 function renderPosts($settings=[]){$w=new \Jednotka\Elementor\Widgets\Posts_Widget;$w->overrides=$settings;return $w->output();}
 $_GET=[];$html=renderPosts();
 check(substr_count($html,'<article class="blog-card">')===6,'six cards, not all 100');
 check(str_contains($html,'Články: 100'),'public post total');
 check($GLOBALS['pagination']['total']===17,'100 articles use 17 pages');
 check(str_contains($html,'loading="lazy"'),'lazy images');
 check(str_contains($html,'assets/images/projects/placeholder.jpg'),'fallback image');
 check(str_contains($html,'aria-label="Web"'),'categories from all posts');
 check(!str_contains($html,'blog-filter--all')&&!str_contains($html,'Všetky'),'no all-categories button');
 check(str_contains($html,'assets/images/services/web.png'),'service image used for Web');
 $GLOBALS['descriptions'][100]='Custom short description <script>bad</script> '.str_repeat('word ',30);$custom=renderPosts();check(str_contains($custom,'Custom short description &lt;script&gt;bad&lt;/script&gt;'),'custom description escaped');check(str_contains($custom,esc_html($GLOBALS['descriptions'][100])),'custom description used in full');unset($GLOBALS['descriptions']);

 check($GLOBALS['terms_query']['hide_empty']===false,'include empty WordPress categories');
 foreach(['web.png','services-eshop.svg','email.png','ppc.png','grafika.png','branding.png'] as $image){check(str_contains($html,'assets/images/services/'.$image),'always show service '.$image);}
 $GLOBALS['terms']=[];$_GET=[];$noCategories=renderPosts();check(substr_count($noCategories,'class="blog-filter"')===6,'all six filters without any categories');
 $_GET=['jn_preview_category'=>'service-email'];$missingCategory=renderPosts();check(end($GLOBALS['queries'])['post__in']===[0],'missing category does not query all posts');check(str_contains($missingCategory,'Nič sme nenašli.'),'missing category shows no results');check(str_contains($missingCategory,'data-category="service-email" type="submit" name="jn_preview_category" value="0"'),'empty filter selected and can clear');
 unset($GLOBALS['terms']);$_GET=['jn_preview_category'=>'service-web'];renderPosts();check(end($GLOBALS['queries'])['cat']===1,'service links resolve when the category exists');

 $_GET=['jn_preview_category'=>'1'];$selected=renderPosts();check(str_contains($selected,'data-category="1" type="submit" name="jn_preview_category" value="0"'),'selected filter clears with native form submission');
 $uncategorized=(object)['term_id'=>3,'name'=>'Uncategorized','slug'=>'uncategorized'];
 $web=(object)['term_id'=>1,'name'=>'Web','slug'=>'web'];
 $GLOBALS['terms']=[$uncategorized,$web];$GLOBALS['post_categories']=[$uncategorized];$_GET=[];$uncategorizedHtml=renderPosts();
 check(!str_contains($uncategorizedHtml,'Uncategorized')&&!str_contains($uncategorizedHtml,'class="blog-badge"'),'uncategorized has no filter or badge');
 check(substr_count($uncategorizedHtml,'<article class="blog-card">')===6,'uncategorized posts remain in archive');
 $GLOBALS['post_categories']=[$uncategorized,$web];$mixed=renderPosts();check(str_contains($mixed,'class="blog-badge"'),'uses visible category when uncategorized is first');
 unset($GLOBALS['terms'],$GLOBALS['post_categories']);

 check(strpos($html,'data-current-category')<strpos($html,'class="blog-filters"'),'native submit category takes precedence over hidden input');
 $_GET=['jn_preview_page'=>'17'];$last=renderPosts();check(substr_count($last,'<article class="blog-card">')===4,'last page has four posts');
 $_GET=['jn_preview_page'=>'300'];renderPosts();check(end($GLOBALS['queries'])['paged']===17,'stale page clamps to last page');
 $_GET=['jn_preview_search'=>'Article 1','jn_preview_category'=>'1'];$filtered=renderPosts();$q=end($GLOBALS['queries']);check($q['s']==='Article 1'&&$q['cat']===1,'combined search and category query');check(str_contains($filtered,'Article 11'),'search reaches posts beyond initial page');
 $_GET=['jn_preview_search'=>'nothing matches'];$empty=renderPosts();check(str_contains($empty,'Nič sme nenašli.'),'no results');
 $_GET=['jn_preview_search'=>['bad'],'jn_preview_category'=>['bad']];renderPosts();$q=end($GLOBALS['queries']);check($q['s']===''&&!isset($q['cat']),'array input ignored');
 $_GET=['jn_preview_search'=>'<b>Article</b>'];renderPosts(['posts_per_page'=>100]);$q=end($GLOBALS['queries']);check($q['s']==='Article'&&$q['posts_per_page']===24,'sanitize search and cap page size');
 $GLOBALS['fixture']=[];$_GET=[];$empty=renderPosts();check(str_contains($empty,'Zatiaľ tu nie sú žiadne články.'),'empty blog');
 check(!str_contains($empty,'min čítania'),'no reading-time metadata');
 echo "Posts render checks passed (100 posts, search, categories, pagination, empty states).\n";
}
