<?php

$plugin['name'] = 'aro_myadmin';
$plugin['version'] = '1.0';
$plugin['author'] = 'Aeron Glemann';
$plugin['author_uri'] = 'http://electricprism.com/aeron';
$plugin['description'] = 'My administrative panel';
$plugin['type'] = '1';

@include_once('zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

if (@txpinterface == 'admin'){
	add_privs('aro_myadmin_js','1,2,3,4,5,6');
	add_privs('dashboard','1,2,3,4,5,6');

	register_callback('aro_myadmin_js', 'aro_myadmin_js', '', 1);
	register_callback('aro_bootstrap', 'article', '', 1);
	register_callback('aro_dashboard', 'dashboard');

	ob_start('aro_myadmin');
}

function aro_bootstrap() {
	if ($_POST && isset($_POST['p_password'])) header('Location: ?event=dashboard');
}

function aro_load_dash( $label , $dash , $debug=false ) {
	$html = safe_field('Form','txp_form','name=\''.$dash.'\'');
	if ($debug) echo (!$html) ? "No form called $dash".br.n : "Loaded $label dash: [$dash]".br.n;
	return $html;
}

function aro_dashboard() {
	include_once txpath.'/publish.php';
	global $txp_user;

	echo pagetop("Textpattern");

	$debug = false;
	$html = false;

	if( $debug ) echo 'User: ['.$txp_user.']'.br.n;

	# Try loading user-specific form, then priv-specific, else global, else default...
	$label = 'user-specific';
	if( !empty($txp_user)) {
		$dash = 'aro_myadmin_dash_' . $txp_user;
		$html = aro_load_dash( $label, $dash, $debug );
	}
	else {
		if ($debug) echo 'Skipped lookup of '.$label.' dashboard.'.br.n;
	}

	$label = 'priv-specific';
	if (!empty($txp_user) && !$html) {
		global $privs;
		if( isset($privs)) {
			$dash = 'aro_myadmin_dash_' . $privs;
			$html = aro_load_dash( $label, $dash, $debug );
		}
	} else {
		if ($debug) echo 'Skipped lookup of '.$label.' dashboard.'.br.n;
	}

	$label = 'global';
	if( !$html ) {
		$dash = 'aro_myadmin_dash';
		$html = aro_load_dash( $label, $dash, $debug );
	}
	else {
		if ($debug) echo 'Skipped lookup of '.$label.' dashboard.'.br.n;
	}
	unset($label);


	if (!$html){
		if ($debug) echo 'Using default dashboard.'.br.n;
		$html = <<<html
<div class="dashboard">
  <div>
    <h2><txp:php>echo gTxt('articles');</txp:php></h2>

		<txp:php>
\$out = array();

\$rows = safe_rows_start("Title, ID",'textpattern',"1=1 ORDER BY LastMod DESC LIMIT 5");

if (\$rows){
	\$out[] = '<h3>'.gTxt('recent_articles').'</h3>';
	\$out[] = '<ul class="plain-list">';

	while (\$row = nextRow(\$rows)){
	  if (!\$row['Title']) \$row['Title'] = gTxt('untitled').sp.\$row['ID'];

	  \$out[] = '<li><a href="?event=article'.a.'step=edit'.a.'ID='.\$row['ID'].'">'.escape_title(\$row['Title']).'</a></li>';
	}
	\$out[] = '</ul>';
}
else {
	\$out[] = '<p>'.gTxt('no_results_found').'</p>';
}

echo join(n, \$out);
		</txp:php>

    <ul class="inline">
	    <li><a href="?event=article"><txp:php>echo ucfirst(mb_strtolower(gTxt('create_new').' '.gTxt('article'), 'UTF-8'));</txp:php></a></li>
	    <li><a href="?event=list"><txp:php>echo ucfirst(mb_strtolower(gTxt('more').' '.gTxt('articles'), 'UTF-8'));</txp:php></a></li>
     </ul>
  </div><!-- end articles -->

  <div>
    <h2><txp:php>echo gTxt('categories');</txp:php></h2>

		<txp:php>
\$out = array();

\$rows = safe_rows_start("id, name, title",'txp_category',"lft > 1 AND type = 'article' ORDER BY title ASC");

if (\$rows){
	\$out[] = '<table class="list"><thead><tr><th></th><th>'.gTxt('articles').'</th></tr></thead><tbody>';

	while (\$row = nextRow(\$rows)){
		\$count = safe_count("textpattern", "Category1 = '\$row[name]' OR Category2 = '\$row[name]'");

	  \$out[] = '<tr><th><span><a href="?event=category'.a.'step=cat_article_edit'.a.'id='.\$row['id'].'">'.escape_title(\$row['title']).'</a></span></th><td><span><a href="?event=list'.a.'search_method=categories'.a.'crit='.\$row['name'].'">'.\$count.'</a></span></td></tr>';
	}

	\$out[] = '</tbody></table>';
}
else {
	\$out[] = '<p>'.gTxt('no_results_found').'</p>';
}

echo join(n, \$out);
		</txp:php>

    <ul class="inline">
	    <li><a href="?event=category"><txp:php>echo ucfirst(mb_strtolower(gTxt('more').' '.gTxt('categories'), 'UTF-8'));</txp:php></a></li>
     </ul>
  </div><!-- end categories -->

  <div style="clear: left">
    <h2><txp:php>echo gTxt('sections');</txp:php></h2>

		<txp:php>
\$out = array();

\$rows = safe_rows_start("name, title",'txp_section',"name != 'default' ORDER BY title ASC");

if (\$rows){
	\$out[] = '<table class="list"><thead><tr><th></th><th>'.gTxt('articles').'</th></tr></thead><tbody>';

	while (\$row = nextRow(\$rows)){
		\$count = safe_count("textpattern", "Section = '\$row[name]'");

	  \$out[] = '<tr><th><span>'.escape_title(\$row['title']).'</span></th><td><span><a href="?event=list'.a.'search_method=section'.a.'crit='.\$row['name'].'">'.\$count.'</a></span></td></tr>';
	}

	\$out[] = '</tbody></table>';
}
else {
	\$out[] = '<p>'.gTxt('no_results_found').'</p>';
}

echo join(n, \$out);
		</txp:php>

    <ul class="inline">
	    <li><a href="?event=section"><txp:php>echo ucfirst(mb_strtolower(gTxt('more').' '.gTxt('sections'), 'UTF-8'));</txp:php></a></li>
     </ul>
  </div><!-- end sections -->

  <div>
    <h2><txp:php>echo gTxt('site_administration');</txp:php></h2>

		<txp:php>
\$out = array();

\$rows = safe_rows_start('*', 'txp_users', '1=1 ORDER BY name ASC');

\$out[] = '<table class="list"><thead><tr><th></th><th>Last Login</th></tr></thead><tbody>';

\$levels = array(
	1 => gTxt('publisher'),
	2 => gTxt('managing_editor'),
	3 => gTxt('copy_editor'),
	4 => gTxt('staff_writer'),
	5 => gTxt('freelancer'),
	6 => gTxt('designer'),
	0 => gTxt('none')
);

while (\$row = nextRow(\$rows)){
  \$out[] = '<tr><th><span>'.htmlspecialchars(\$row['RealName']).', '.\$levels[\$row['privs']].'</span></th><td><span>'.since(strtotime(\$row['last_access'])).'</span></td></tr>';
}

\$out[] = '</tbody></table>';

echo join(n, \$out);
		</txp:php>

    <ul class="inline">
    	<li><a href="?event=admin"><txp:php>echo ucfirst(mb_strtolower(gTxt('more').' '.gTxt('authors'), 'UTF-8'));</txp:php></a></li>
    </ul>
  </div><!-- end users -->
</div>
html;
	}

	echo parse($html);

	echo end_page();
}

function aro_myadmin_js(){
	while (@ob_end_clean());

	header("Content-type: text/javascript");

  $js = array();

  for($i = 1; $i <= 10; $i++){
    $arr = array();

    $rows = safe_rows("DISTINCT custom_$i", "textpattern", "1 = 1 ORDER BY custom_$i ASC");
    foreach($rows as $row){
      if (!empty($row["custom_$i"])) $arr[] = addslashes($row["custom_$i"]);
    }

    $js[] = "\$('#custom-$i').autocomplete({ list: ['".implode("', '", $arr)."'], match: function(str) { return this.match(new RegExp('^' + str, 'i')); }, timeout: 0 });";
  }

  $js = implode("\n\t", $js);

	echo <<<js
// color.js
(function(jQuery){jQuery.each(['backgroundColor','borderBottomColor','borderLeftColor','borderRightColor','borderTopColor','color','outlineColor'],function(i,attr){jQuery.fx.step[attr]=function(fx){if(fx.state==0){fx.start=getColor(fx.elem,attr);fx.end=getRGB(fx.end);}
fx.elem.style[attr]="rgb("+[Math.max(Math.min(parseInt((fx.pos*(fx.end[0]-fx.start[0]))+fx.start[0]),255),0),Math.max(Math.min(parseInt((fx.pos*(fx.end[1]-fx.start[1]))+fx.start[1]),255),0),Math.max(Math.min(parseInt((fx.pos*(fx.end[2]-fx.start[2]))+fx.start[2]),255),0)].join(",")+")";}});function getRGB(color){var result;if(color&&color.constructor==Array&&color.length==3)
return color;if(result=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
return[parseInt(result[1]),parseInt(result[2]),parseInt(result[3])];if(result=/rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
return[parseFloat(result[1])*2.55,parseFloat(result[2])*2.55,parseFloat(result[3])*2.55];if(result=/#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
return[parseInt(result[1],16),parseInt(result[2],16),parseInt(result[3],16)];if(result=/#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
return[parseInt(result[1]+result[1],16),parseInt(result[2]+result[2],16),parseInt(result[3]+result[3],16)];return colors[jQuery.trim(color).toLowerCase()];}
function getColor(elem,attr){var color;do{color=jQuery.curCSS(elem,attr);if(color!=''&&color!='transparent'||jQuery.nodeName(elem,"body"))
break;attr="backgroundColor";}while(elem=elem.parentNode);return getRGB(color);};})(jQuery);

// autocomplete.js
(function(\$){\$.ui=\$.ui||{};\$.ui.autocomplete=\$.ui.autocomplete||{};var active;\$.fn.autocompleteMode=function(container,input,size,opt){var original=input.val();var selected=-1;var self=this;\$.data(document.body,"autocompleteMode",true);\$("body").one("cancel.autocomplete",function(){input.trigger("cancel.autocomplete");\$("body").trigger("off.autocomplete");input.val(original);});\$("body").one("activate.autocomplete",function(){input.trigger("activate.autocomplete",[\$.data(active[0],"originalObject")]);\$("body").trigger("off.autocomplete");});\$("body").one("off.autocomplete",function(e,reset){container.remove();\$.data(document.body,"autocompleteMode",false);input.unbind("keydown.autocomplete");\$("body").add(window).unbind("click.autocomplete").unbind("cancel.autocomplete").unbind("activate.autocomplete");});\$(window).bind("click.autocomplete",function(){\$("body").trigger("cancel.autocomplete");});var select=function(){active=\$("> *",container).removeClass("active").slice(selected,selected+1).addClass("active");input.trigger("itemSelected.autocomplete",[\$.data(active[0],"originalObject")]);input.val(opt.insertText(\$.data(active[0],"originalObject")));};container.mouseover(function(e){if(e.target==container[0])return;selected=\$("> *",container).index(\$(e.target).is('li')?\$(e.target)[0]:\$(e.target).parents('li')[0]);select();}).bind("click.autocomplete",function(e){\$("body").trigger("activate.autocomplete");\$.data(document.body,"suppressKey",false);});input.bind("keydown.autocomplete",function(e){if(e.which==27){\$("body").trigger("cancel.autocomplete");}
else if(e.which==13){\$("body").trigger("activate.autocomplete");}
else if(e.which==40||e.which==9||e.which==38){switch(e.which){case 40:case 9:selected=selected>=size-1?0:selected+1;break;case 38:selected=selected<=0?size-1:selected-1;break;default:break;}
select();}else{return true;}
\$.data(document.body,"suppressKey",true);});};\$.fn.autocomplete=function(opt){opt=\$.extend({},{timeout:1000,getList:function(input){input.trigger("updateList",[opt.list]);},template:function(str){return"<li>"+opt.insertText(str)+"</li>";},insertText:function(str){return str;},match:function(typed){return this.match(new RegExp(typed));},wrapper:"<ul class='jq-ui-autocomplete'></ul>"},opt);if(\$.ui.autocomplete.ext){for(var ext in \$.ui.autocomplete.ext){if(opt[ext]){opt=\$.extend(opt,\$.ui.autocomplete.ext[ext](opt));delete opt[ext];}}}
return this.each(function(){\$(this).keypress(function(e){var typingTimeout=\$.data(this,"typingTimeout");if(typingTimeout)window.clearInterval(typingTimeout);if(\$.data(document.body,"suppressKey"))
return \$.data(document.body,"suppressKey",false);else if(\$.data(document.body,"autocompleteMode")&&e.charCode<32&&e.keyCode!=8&&e.keyCode!=46)return false;else{\$.data(this,"typingTimeout",window.setTimeout(function(){\$(e.target).trigger("autocomplete");},opt.timeout));}}).bind("autocomplete",function(){var self=\$(this);self.one("updateList",function(e,list){list=\$(list).filter(function(){return opt.match.call(this,self.val());}).map(function(){var node=\$(opt.template(this))[0];\$.data(node,"originalObject",this);return node;});\$("body").trigger("off.autocomplete");if(!list.length)return false;var container=list.wrapAll(opt.wrapper).parents(":last").children();var offset=self.offset();opt.container=container.css({top:offset.top+self.outerHeight(),left:offset.left,width:self.width()}).appendTo("body");\$("body").autocompleteMode(container,self,list.length,opt);});opt.getList(self);});});};})(jQuery);

// dimensions.js
(function(\$){\$.dimensions={version:'@VERSION'};\$.each(['Height','Width'],function(i,name){\$.fn['inner'+name]=function(){if(!this[0])return;var torl=name=='Height'?'Top':'Left',borr=name=='Height'?'Bottom':'Right';return this.css('display')!='none'?this[0]['client'+name]:num(this,name.toLowerCase())+num(this,'padding'+torl)+num(this,'padding'+borr);};\$.fn['outer'+name]=function(options){if(!this[0])return;var torl=name=='Height'?'Top':'Left',borr=name=='Height'?'Bottom':'Right';options=\$.extend({margin:false},options||{});var val=this.css('display')!='none'?this[0]['offset'+name]:num(this,name.toLowerCase())
+num(this,'border'+torl+'Width')+num(this,'border'+borr+'Width')
+num(this,'padding'+torl)+num(this,'padding'+borr);return val+(options.margin?(num(this,'margin'+torl)+num(this,'margin'+borr)):0);};});\$.each(['Left','Top'],function(i,name){\$.fn['scroll'+name]=function(val){if(!this[0])return;return val!=undefined?this.each(function(){this==window||this==document?window.scrollTo(name=='Left'?val:\$(window)['scrollLeft'](),name=='Top'?val:\$(window)['scrollTop']()):this['scroll'+name]=val;}):this[0]==window||this[0]==document?self[(name=='Left'?'pageXOffset':'pageYOffset')]||\$.boxModel&&document.documentElement['scroll'+name]||document.body['scroll'+name]:this[0]['scroll'+name];};});\$.fn.extend({position:function(){var left=0,top=0,elem=this[0],offset,parentOffset,offsetParent,results;if(elem){offsetParent=this.offsetParent();offset=this.offset();parentOffset=offsetParent.offset();offset.top-=num(elem,'marginTop');offset.left-=num(elem,'marginLeft');parentOffset.top+=num(offsetParent,'borderTopWidth');parentOffset.left+=num(offsetParent,'borderLeftWidth');results={top:offset.top-parentOffset.top,left:offset.left-parentOffset.left};}
return results;},offsetParent:function(){var offsetParent=this[0].offsetParent;while(offsetParent&&(!/^body|html\$/i.test(offsetParent.tagName)&&\$.css(offsetParent,'position')=='static'))
offsetParent=offsetParent.offsetParent;return \$(offsetParent);}});function num(el,prop){return parseInt(\$.curCSS(el.jquery?el[0]:el,prop,true))||0;};})(jQuery);

\$(document).ready(function(){
	// make stripey tables
	\$(".ruled tr:not(:has(select))").mouseover(function(){
		\$(this).addClass("hover");
	}).mouseout(function(){
		\$(this).removeClass("hover");
	});
	\$(".ruled tr:even").addClass("rule");

	// fix th padding
	\$(".ruled th:not(:has(a))").each(function(){
		$(this).contents().wrap("<span/>")
	});

	// fade our messages
	\$(".message").animate({ backgroundColor: '#FFFFFF' }, 4000);

	// target multiple selects
	\$("select[multiple]").addClass("multiple");

	// override boring element toggle
	window.toggleDisplay = function(id){
		\$('#' + id).slideToggle(500);
	};

	// add auto-completes to our custom fields
	$js
});
js;
	exit;
}

function aro_myadmin($buffer){
	# If possible, look at the headers to see what's being served...
	if(is_callable('headers_list'))
	{
		$hlist = headers_list();
		$content_type = '';
		if( !empty( $hlist ) )
		{
			foreach( $hlist as $header )
			{
				$header = strtolower( $header );
				$r = strstr( $header, 'content-type' );
				if( false === $r ) continue;

				$r = strstr( $header, 'text/html' );
				if( false === $r ) continue;

				$content_type = 'text/html';
				break;  
			}
		}
		# Don't attempt to modify requests which don't output HTML... ie skip any buffers which have JS/CSS/XML etc... 
		if( $content_type !== 'text/html' )
			return $buffer;
	}

	global $sitename,$txp_user;

	// add our stylesheet and favicon
	$find = '@(<link.*href="textpattern.css".*/>)@U';
	$replace = '\1 <link href="myadmin.css" rel="stylesheet" type="text/css" /><link rel="shortcut icon" href="txp_img/favicon.ico" type="image/ico" />';
	$tmp_buffer = preg_replace($find, $replace, $buffer, 1);
	if( NULL === $tmp_buffer )
		return $buffer; # preg_replace failed.
	else
	{
		# The preg_ call didn't fail -- but did it find the textpattern.css string? ...
		#
		# We can't use preg_replace's count parameter as it's php 5+ only. 
		# Resort to length comparison to see if the replace did anything to the string...  
		$orig_len = strlen( $buffer );
		$new_len  = strlen( $tmp_buffer );
		if( $new_len === $orig_len )
			return $buffer; # textpattern.css not found so this is probably not a standard textpattern page -- don't try any more inserts
		else
			$buffer = $tmp_buffer; # It's a standard txp page -- go on with Aeron's replacements...
	}

	// check for message
	$pattern = '@<td valign="middle" style="width:368px">&nbsp;(.*)</td>@U';
	preg_match($pattern, $buffer, $matches);

	$message = ($matches) ? $matches[1] : '';

	// is it a stripey table?
	$events = array('list', 'image', 'file', 'link', 'discuss', 'log');
	$steps = array('image_edit', 'file_edit');

	if (in_array(gps('event'), $events) && !in_array(gps('step'), $steps)){
		$find = '@(<table.*id="list")@U';
		$replace = '\1 class="ruled"';

		$tmp_buffer = preg_replace($find, $replace, $buffer);
		if( NULL !== $tmp_buffer )
			$buffer = $tmp_buffer;
	}

	if ($txp_user){ // logged in
		// add our javascript
		$find = '<script type="text/javascript" src="jquery.js"></script>';
		$replace = '<script type="text/javascript" src="index.php?event=aro_myadmin_js"></script>';

		$buffer = str_replace($find, $find.n.$replace, $buffer);

		// replace header
	 	$find = '@<table id="pagetop".*</tr></table></td></tr></table>@sU';
		$replace = aro_pagetop($message);
		$tmp_buffer = preg_replace($find, $replace, $buffer, 1);
		if( NULL !== $tmp_buffer )
			$buffer = $tmp_buffer;

		// replace footer
	 	$find = '@<div id="end_page".*</div>@s';
		$replace = aro_end_page(true);
		$tmp_buffer = preg_replace($find, $replace, $buffer, 1);
		if( NULL !== $tmp_buffer )
			$buffer = $tmp_buffer;
	} else {
		// replace header
	 	$find = '@<table id="pagetop".*</tr></table></td></tr></table>@s';
		$replace = aro_pagetop($message);
		$tmp_buffer = preg_replace($find, $replace, $buffer, 1);
		if( NULL !== $tmp_buffer )
			$buffer = $tmp_buffer;

		// add our sitename
		$find = gTxt('login_to_textpattern');
		$replace = str_replace('textpattern', $sitename, strtolower(gTxt('login_to_textpattern')));

		$buffer = str_replace($find, $replace, $buffer);

		// replace footer
	 	$find = '@</body>@s';
		$replace = aro_end_page().'</body>';

		$tmp_buffer = preg_replace($find, $replace, $buffer, 1);
		if( NULL !== $tmp_buffer )
			$buffer = $tmp_buffer;
	}

	return $buffer;
}

function aro_pagetop($message){
	global $siteurl,$sitename,$txp_user,$event;

	$area = gps('event');
	$event = (!$event) ? 'article' : $event;
	$bm = gps('bm');

	$privs = safe_field("privs", "txp_users", "name = '".doSlash($txp_user)."'");

	$GLOBALS['privs'] = $privs;

	$areas = areas();

	foreach ($areas as $k => $v){
		if (in_array($event, $v)){
			$area = $k;
			break;
		}
	}

	$out[] = '<div id="header">';
	$out[] = '<h1 class="branding"><a href="'.hu.'" title="'.gTxt('tab_view_site').'"><img src="txp_img/sitelink.gif" alt="'.$sitename.'" /></a></h1>';

	if ($txp_user)
	{
		$ev = (has_privs('prefs')) ? 'prefs' : 'admin' ;
		$lang_sel = (is_callable('_l10n_inject_switcher_form')) ? _l10n_inject_switcher_form() . ' | ' : '';
		$out[] = '<div class="user">'.$txp_user.' - '.$lang_sel.'<a href="index.php?event='.$ev.'">'.gTxt('prefs').'</a> | <a href="index.php?logout=1">'.gTxt('logout').'</a></div>';
	}

	if (!$bm && $txp_user){
		// primary navigation
		$out[] = '<ul id="nav-primary">';
		$out[] = has_privs('tab.content') ? aro_areatab(gTxt('Dashboard'), 'dashboard', 'dashboard', $area) : '';
		$out[] = has_privs('tab.content') ? aro_areatab(gTxt('tab_content'), 'content', 'article', $area) : '';
		$out[] = has_privs('tab.presentation') ? aro_areatab(gTxt('tab_presentation'), 'presentation', 'page', $area) : '';
		$out[] = '</ul>';

		// secondary navigation
		$out[] = '<ul id="nav-secondary">';
		$out[] = has_privs('tab.admin') ? aro_areatab(gTxt('tab_admin'), 'admin', 'admin', $area) : '';
		$out[] = (has_privs('tab.extensions') && !empty($areas['extensions'])) ? aro_areatab(gTxt('tab_extensions'), 'extensions', array_shift($areas['extensions']), $area) : '';
		$out[] = '</ul>';

		$out[] = '</div><!-- end header -->';
		$out[] = '<div id="myadmin_body">';

		// terciary navigation
		$out[] = '<ul id="nav-terciary">';
		$out[] = aro_tabsort($area, $event);
		$out[] = '</ul>';
	}
	else {
		$out[] = '</div><!-- end header -->';
		$out[] = '<div id="myadmin_body">';
	}

	$out[] = '<div id="content">';

	if ($message) $out[] = '<div class="message">'.$message.'</div>';

	return join(n, $out);
}

function aro_areatab($label,$event,$tarea,$area){
	$tc = ($area == $event) ? 'tabup' : 'tabdown';
	$atts=' class="'.$tc.'"';
	$hatts=' href="?event='.$tarea.'" class="plain"';

	$a = tag($label, 'a', $hatts);
	$ul = tag(aro_tabsort($event, $tarea), 'ul');

	return tag($a.$ul, 'li', $atts);
}

function aro_tabsort($area,$event){
	if ($area){
		$areas = areas();

		if (isset($areas[$area])) {
			foreach($areas[$area] as $a => $b){
				if (has_privs($b)){
					$out[] = aro_tabber($a, $b, $event, 2);
				}
			}
			return ($out) ? join('', $out) : '';
		}
	}

	return '';
}

function aro_tabber($label,$tabevent,$event){
	$tc = ($event==$tabevent) ? 'tabup' : 'tabdown2';
	$atts=' class="'.$tc.'"';
	$hatts=' href="?event='.$tabevent.'" class="plain"';

	$a = tag($label, 'a', $hatts);

	return tag($a, 'li', $atts);
}

function aro_end_page($content){
	$out[] = ($content) ? '</div><!-- end content -->' : '';
	$out[] = '<p class="textpattern">Powered by <a href="http://www.textpattern.com">Textpattern</a></p>';
	$out[] = '</div><!-- end body -->';

	return join(n, $out);
}

# --- END PLUGIN CODE ---
if (0) {
?>
<!--
# --- BEGIN PLUGIN HELP ---

h1. aro_myadmin

Just copy myadmin.css to the /textpattern folder and the included images to the /textpattern/txp_image folder. Install and activate the plugin and you&#8217;re good to go. If you want the real deal though, you will have to add 1 line to /textpattern/index.php:

@load_plugin(basename("aro_myadmin"));@

Add the above to ~line 88 of index.php - it must come before doAuth();

Replace the image _sitelink.gif_ and _favicon.ico_ with the appropriate images for your site. Create a form with the name *aro_myadmin_dash* to overwrite the plugin dashboard.

You can give individual adminstrative users their own dashboard form by creating a form called *aro_myadmin_dash_USERLOGIN*. So if you have a user who logs in as 'john' you would need to create his dashboard in the form called 'aro_myadmin_dash_john'.

Alternatively, you can create a custom dashboard for all users of a given privilage level by creating a form called *aro_myadmin_dash_PRIVLEVEL* where PRIVLEVEL is a numeral representing the priv level the form will be used for.

Create a form with the name <strong>aro_myadmin_dash</strong> to overwrite the default plugin dashboard. This dash form will be used if there is no overriding user dash or priv-level dash.


h2. Special Thanks

Special thanks to Steve AKA "Netcarver":http://txp-plugins.netcarving.com/ for his work getting the code to play nice with other TXP plugins.

h2. Contributions

This plugin is open-source, if you would like to make a code contribution please checkout a copy of the plugin from the "repository":http://github.com/rloaderro/aro_myadmin/tree/master.

# --- END PLUGIN HELP ---
-->
<?php
}
?>