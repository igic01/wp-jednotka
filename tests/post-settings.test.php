<?php
/** Isolated category provisioning and post sidebar persistence checks. */
define( 'ABSPATH', __DIR__ );
define( 'JEDNOTKA_ELEMENTOR_URL', '/' );
class WP_Error { public $code; public function __construct($code,$message='',$data=array()){$this->code=$code;} }
function is_wp_error($value){return $value instanceof WP_Error;}
function sanitize_title($value){return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/','-',$value),'-'));}
function get_terms($args){return array_values($GLOBALS['terms']);}
function get_option($key){return $GLOBALS['options'][$key]??false;}
function update_option($key,$value,$autoload=false){$GLOBALS['options'][$key]=$value;}
function wp_insert_term($name,$taxonomy,$args){
 $allowed=\Jednotka\Elementor\Post_Settings::restrict_creation($name,$taxonomy,$args);
 if(is_wp_error($allowed))return $allowed;
 $id=count($GLOBALS['terms'])+100;$GLOBALS['terms'][$id]=(object)['term_id'=>$id,'slug'=>$args['slug'],'name'=>$name];return ['term_id'=>$id];
}
function get_taxonomy($key){return $GLOBALS['taxonomy'];}
function register_post_meta($type,$key,$args){$GLOBALS['meta_registration']=$args;}
function wp_is_post_revision($id){return $GLOBALS['revision']??false;}
function current_user_can($cap,$id=null){return $GLOBALS['can_edit']??true;}
function wp_verify_nonce($nonce,$action){return $nonce==='valid';}
function sanitize_text_field($value){return trim(strip_tags($value));}
function sanitize_textarea_field($value){return trim(strip_tags($value));}
function wp_unslash($value){return stripslashes($value);}
function absint($value){return abs((int)$value);}
function update_post_meta($id,$key,$value){$GLOBALS['saved_meta'][$id][$key]=$value;}
function get_post_meta($id,$key,$single){return $GLOBALS['saved_meta'][$id][$key]??'';}
function wp_set_post_categories($id,$categories){$GLOBALS['assigned'][$id]=$categories;}
function wp_get_post_categories($id){return $GLOBALS['assigned'][$id]??[];}
function wp_nonce_field($action,$name){echo '<input type="hidden" name="'.$name.'" />';}
function checked($a,$b,$echo=true){return $a===$b?'checked="checked"':'';}
function esc_attr($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function esc_html($value){return esc_attr($value);}
function esc_textarea($value){return esc_attr($value);}
function esc_url($value){return esc_attr($value);}
function check($value,$label){if(!$value)throw new RuntimeException($label);}
require dirname(__DIR__).'/includes/class-post-settings.php';
use Jednotka\Elementor\Post_Settings;
$GLOBALS['terms']=[10=>(object)['term_id'=>10,'slug'=>'web','name'=>'Web'],11=>(object)['term_id'=>11,'slug'=>'reklamy','name'=>'Reklamy'],12=>(object)['term_id'=>12,'slug'=>'old-category','name'=>'Old category']];
$GLOBALS['taxonomy']=(object)['cap'=>(object)['manage_terms'=>'manage_categories','edit_terms'=>'manage_categories','delete_terms'=>'manage_categories','assign_terms'=>'edit_posts']];
Post_Settings::setup();
check(count(Post_Settings::terms())===6,'provisions six categories');
check(Post_Settings::terms()['web']->term_id===10&&Post_Settings::terms()['ppc']->term_id===11,'reuses existing service categories and aliases');
check(isset($GLOBALS['terms'][12]),'preserves legacy categories');
$count=count($GLOBALS['terms']);Post_Settings::setup();check(count($GLOBALS['terms'])===$count,'idempotent setup');
check($GLOBALS['taxonomy']->cap->manage_terms==='do_not_allow'&&$GLOBALS['taxonomy']->cap->assign_terms==='edit_posts','blocks management while retaining assignment');
check(is_wp_error(Post_Settings::restrict_creation('Extra','category')),'category creation blocked');
check(Post_Settings::restrict_creation('Tag','post_tag')==='Tag','tags unaffected');
check($GLOBALS['meta_registration']['sanitize_callback']==='sanitize_textarea_field','REST meta sanitized');
$post=(object)['ID'=>42,'post_type'=>'post'];
$_POST=['jednotka_description_nonce'=>'valid','jednotka_short_description'=>"<b>Short</b>\nDescription",'jednotka_categories_nonce'=>'valid','jednotka_categories_present'=>'1','jednotka_categories'=>['10','12','999']];
Post_Settings::save(42,$post);
check($GLOBALS['saved_meta'][42][Post_Settings::DESCRIPTION]==="Short\nDescription",'description saved as plain text with line breaks');
check($GLOBALS['assigned'][42]===[10],'only service categories assigned');
$_POST['jednotka_description_nonce']='invalid';$_POST['jednotka_short_description']='Wrong';Post_Settings::save(42,$post);check($GLOBALS['saved_meta'][42][Post_Settings::DESCRIPTION]!== 'Wrong','invalid nonce cannot save');
$_POST['jednotka_description_nonce']='valid';$GLOBALS['can_edit']=false;Post_Settings::save(42,$post);check($GLOBALS['saved_meta'][42][Post_Settings::DESCRIPTION]!== 'Wrong','unauthorized save ignored');
$GLOBALS['can_edit']=true;$GLOBALS['revision']=true;Post_Settings::save(42,$post);check($GLOBALS['saved_meta'][42][Post_Settings::DESCRIPTION]!== 'Wrong','revision ignored');$GLOBALS['revision']=false;
$_POST=[];Post_Settings::save(42,$post);check($GLOBALS['saved_meta'][42][Post_Settings::DESCRIPTION]!== 'Wrong','absent sidebar fields do not erase values');
$_POST=['jednotka_description_nonce'=>'valid','jednotka_short_description'=>''];Post_Settings::save(42,$post);check($GLOBALS['saved_meta'][42][Post_Settings::DESCRIPTION]==='','description can be cleared');
$request=new class {public $ids=[12];function get_param($key){return $this->ids;}};
check(is_wp_error(Post_Settings::validate_categories($post,$request)),'REST rejects unsupported categories');$request->ids=[10];check(Post_Settings::validate_categories($post,$request)===$post,'REST permits service categories');
check(count(Post_Settings::rest_categories([])['include'])===6,'REST category picker limited to six services');
ob_start();Post_Settings::categories_box($post);$html=ob_get_clean();check(substr_count($html,'type="checkbox"')===6,'six sidebar choices');check(substr_count($html,'assets/images/services/')===6,'all choices have images');check(!str_contains($html,'Old category'),'legacy category not offered');
ob_start();Post_Settings::description_box($post);$html=ob_get_clean();check(str_contains($html,'<textarea'),'sidebar description textarea');
echo "Post settings checks passed (provisioning, restrictions, sidebar, secure saves, REST).\n";
