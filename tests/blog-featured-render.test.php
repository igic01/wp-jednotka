<?php
/** Standalone featured article checks; WordPress post APIs are stubbed. */
namespace Elementor {
 class Controls_Manager { const SELECT2='select2',TEXT='text',TEXTAREA='textarea',COLOR='color',RAW_HTML='raw',GALLERY='gallery',MEDIA='media',SELECT='select',REPEATER='repeater',SWITCHER='switcher',NUMBER='number',URL='url',TAB_STYLE='style',SLIDER='slider',DIMENSIONS='dimensions'; }
 class Group_Control_Typography { static function get_type(){ return 'typography'; } }
 class Plugin { public static $instance; }
 class EditorStub { public $editing=false; function is_edit_mode(){return $this->editing;} }
 class Repeater {
  protected $controls=[]; function add_control($key,$args){if(isset($this->controls[$key]))throw new \RuntimeException("Duplicate Elementor control: ".$key);$this->controls[$key]=$args;} function get_controls(){return $this->controls;} function add_responsive_control($key,$args){$this->add_control($key,$args);} function add_group_control($type,$args){}
 }
 class Widget_Base extends Repeater {

  function __construct(){ $this->register_controls(); }
  function start_controls_section($id,$args){$this->add_control($id,array_merge($args,['type'=>'section']));} function end_controls_section(){}
  function add_control($key,$args){if(isset($this->controls[$key]))throw new \RuntimeException("Duplicate Elementor control: ".$key);$this->controls[$key]=$args;}
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
 function get_the_category($id){return [(object)['name'=>'Web']];}
 function get_the_date($format,$post){return '17. október 2026';}
 function get_permalink($post){return 'https://example.com/post/'.$post->ID;}
 function get_the_post_thumbnail_url($post,$size){return $post->thumbnail??false;}
 \Elementor\Plugin::$instance=(object)['editor'=>new \Elementor\EditorStub];
 function current_user_can($cap){return true;} function is_admin(){return false;} function absint($value){return abs((int)$value);}
 function get_post($id){foreach($GLOBALS['posts']??[] as $post)if($post->ID===$id)return $post;return null;}
 function get_post_meta($id,$key,$single){return $GLOBALS['short_description']??'';}
 require dirname(__DIR__).'/widgets/class-blog-featured-widget.php';
 function check($yes,$message){if(!$yes)throw new \RuntimeException($message);}
 $w=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$html=$w->output();
 check(str_contains($html,'Email, ktorý sa číta do konca'),'reference article');
 check(str_contains($html,'data-elementor-setting-key="title"'),'manual inline title');
 check(str_contains($html,'class="blog-featured" href="#"'),'linked article');
 $edited=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$edited->overrides=['title'=>'<script>Title & test</script>','heading_tag'=>'script'];$safe=$edited->output();check(str_contains($safe,'&lt;script&gt;Title &amp; test&lt;/script&gt;'),'escaped title');check(str_contains($safe,'<h1'),'allowlisted heading');
 \Elementor\Plugin::$instance->editor->editing=true;
 $editor=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$editor_html=$editor->output();check(str_contains($editor_html,'<article class="blog-featured"'),'editor card does not navigate');
 \Elementor\Plugin::$instance->editor->editing=false;
 $post=(object)['ID'=>123,'post_type'=>'post','post_status'=>'publish','post_password'=>'','post_title'=>'Real post','post_excerpt'=>'<b>Real excerpt</b>','post_content'=>str_repeat('word ',450),'thumbnail'=>'https://example.com/post.jpg'];$GLOBALS['posts']=[$post];
 \Elementor\Plugin::$instance->editor->editing=true;$picker=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;check(isset($picker->get_controls()['post_id']['options'][123]),'post offered in searchable dropdown');\Elementor\Plugin::$instance->editor->editing=false;
 $latest=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$latest->overrides=['source'=>'latest'];$live=$latest->output();
 check(str_contains($live,'Real post')&&str_contains($live,'Real excerpt'),'latest title and excerpt');check(!str_contains($live,'min čítania')&&str_contains($live,'17. október 2026'),'date without reading time');check(str_contains($live,'https://example.com/post/123'),'real permalink');check(str_contains($live,'https://example.com/post.jpg'),'post thumbnail');
 check(!str_contains($live,'data-elementor-setting-key="title"'),'post data edited in WordPress Posts');check($GLOBALS['query']['has_password']===false&&$GLOBALS['query']['post_status']==='publish','query excludes protected posts');
 $selected=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$selected->overrides=['source'=>'selected','post_id'=>'123'];$selected_html=$selected->output();check(str_contains($selected_html,'Real post')&&!str_contains($selected_html,'data-elementor-setting-key="title"'),'selected post uses WordPress data');
 $GLOBALS['short_description']='Custom short description';$selected=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$selected->overrides=['source'=>'selected','post_id'=>'123'];check(str_contains($selected->output(),'Custom short description'),'uses post short description');unset($GLOBALS['short_description']);
 foreach(['private','draft','publish'] as $status){$post->post_status=$status;$post->post_password=$status==='publish'?'secret':'';$selected=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$selected->overrides=['source'=>'selected','post_id'=>'123'];check(!str_contains($selected->output(),'Real post'),'selected nonpublic post hidden');}
 $post->post_status='publish';$post->post_password='';$post->post_type='page';$selected=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$selected->overrides=['source'=>'selected','post_id'=>'123'];check(!str_contains($selected->output(),'Real post'),'pages excluded');$post->post_type='post';
 foreach(['','999'] as $id){$selected=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$selected->overrides=['source'=>'selected','post_id'=>$id];check(!str_contains($selected->output(),'Real post'),'missing selection never falls back to latest');}
 foreach(['private','draft'] as $status){$post->post_status=$status;$v=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$v->overrides=['source'=>'latest'];check(!str_contains($v->output(),'Real post'),'nonpublic post hidden');}
 $post->post_status='publish';$post->post_password='secret';$v=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$v->overrides=['source'=>'latest'];check(!str_contains($v->output(),'Real post'),'password-protected post hidden');
 $GLOBALS['posts']=[];$empty=new \Jednotka\Elementor\Widgets\Blog_Featured_Widget;$empty->overrides=['source'=>'latest'];check(str_contains($empty->output(),'Zatiaľ tu nie sú žiadne články.'),'empty blog state');
 echo "Blog featured render checks passed.\n";
}
