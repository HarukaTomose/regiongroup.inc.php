<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: regiongroup.inc.php,v 1.1 2025  Tomose
// Based on region.inc.php,v 1.2 2005/01/22 15:50:00 xxxxx Exp $
//
// いろいろ考えた結果、根本的な見直しを行う。
// ・Javascriptを作るので、1回しか呼び出せない部分があるのは妥当。
// ・だが「開く」「閉じる」ボタンが1か所でしか作れないのはおかしい。
//
// usage
// #regiongroup(mode)
//	mode: ボタン表示の仕様
//		無指定 or default: Table形式のタッチエリア（過去互換動作）
//		image: アイコン表示・縦置きモード
//				「[+]すべて開く」と「[-]すべて閉じる」という『アイコン+文字』表示
//				これらの２つが縦に並んでいる形
//		image_vertical : image 指定と同じ動作
//		image_horizontal : 『アイコン+文字』表示。
//				「開く」と「閉じる」を横に並べている
//
// 本プラグインをインストールする場合、pukiwiki/imageディレクトリに次の２つの
// 画像ファイルを置いてください（こちらの画像は友瀬からは配布していません）
//		ExpandAll.png :	「すべて開く」アイコン
//		CollapseAll : 	「すべて閉じる」アイコン
// こちらの画像については、友瀬は配布していません。各自で準備ください。


function plugin_regiongroup_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new RegiongroupPluginHTMLBuilder();
	
	//今までは画面表示は「table形式でのクリックエリア固定」だったが、
	//他の手段でもよい形にする。
	// その条件をパラメータとして受け取れるようにする。
	// 互換性のため、最初のパラメータは空白を許容。
	$args = func_get_args();
	$prm= array();

	$prm['mode']=array_shift($args);
	if($prm['mode']=='') $prm['mode']='default';

	foreach( $args as $key => $arg){		
		if($key==0){
			//第一パラメータ。空欄なら従来通り、それ以外なら別動作。
			$prm['mode']=$arg;
			if($arg=='') $prm['mode']='default';
			
		}else	if(strpos($arg,'=')){
			// prmname = xxx 形式の引数。
			$argss= explode('=',$arg);
			$argss = array_map('trim', $argss);
			switch ($argss[0])	{
				case 'prmname':
					// $argss[1]の値で処理
					break;
				default:
					break;

			}
		}
		else{
			//prmname無。$keyの位置で処理するしかない。
			switch ($key)
			{
				default:
					break;

			}

		}
		
	}

	// ＨＴＭＬ返却
	return $builder->build($prm['mode']);
}


// クラスの作り方⇒http://php.s3.to/man/language.oop.object-comparison-php4.html
class RegiongroupPluginHTMLBuilder
{
	var $description;
	var $isopened;
	var $scriptVarName;
	var $id_buttonopen,$id_buttonclose;


	//↓ buildメソッドを呼んだ回数をカウントする。
	// １つのHTMLには Javascriptを 1回だけしか出力させないための処理。
	var $callcount;

	function RegiongroupPluginHTMLBuilder() {
		$this->callcount = 0;
		$this->setDefaultSettings();
		$this->id_buttonopen="rgn_opener_opentxt";
		$this->id_buttonclose="rgn_opener_closetxt";
	}
	function setDefaultSettings(){
		$this->description = "...";
		$this->isopened = false;
	}

	function setClosed(){ $this->isopened = false; }
	function setOpened(){ $this->isopened = true; }

	// convert_html()を使って、概要の部分にブランケットネームを使えるように改良。
	function setDescription($description){ $this->description = "aaa"; }

	function build($mode){
		//呼び出された回数をカウント。
		$this->callcount++;
		$html = array();
		// 以降、ＨＴＭＬ作成処理

		array_push( $html, $this->buildButtonHtml($mode) );

		return join($html);
		
	}

	// ■ ボタンの部分。
	function buildButtonHtml($mode){
		switch ($mode)	{
			// $argss[1]の値で処理
			case 'image':
			case 'image_vertical':
				$button = $this->buildImagebutton(0);
				break;

			case 'image_horizontal':
				$button = $this->buildImagebutton(1);
				break;


			default:
				$button = $this->buildDefaultbutton();
				break;

		}

		//初回呼び出し時のみ、Javascriptを追加で吐き出す。
		if( $this->callcount ==1) {
			$button .= $this->buildJavascript();
		}
		return $button;
	}



	function buildImagebutton($mode){
	
		$imgopen = IMAGE_DIR."ExpandAll";
		$imgclose = IMAGE_DIR."CollapseAll";

		$sep = ($mode==1)?'':'<br />';

		$button = "<div>".$this->buildOpenImagebutton().$sep.
			$this->buildCloseImagebutton()."</div>";

		return $button;

	}


	function buildOpenImagebutton(){
		$imgopen = IMAGE_DIR."ExpandAll";
		$button = 
<<<EOD
<span style='cursor:pointer;' onclick='regiongroupe_allopen();'>
<img id='rgngroup_btn_open' src='$imgopen.png' title='すべて開く' />
<span id='rgn_opener_opentxt'>すべて開く</span>
</span>
EOD;
		return $button;

	}

	function buildCloseImagebutton(){
		$imgclose = IMAGE_DIR."CollapseAll";
		$button = 
<<<EOD
<span style="cursor:pointer;" onclick="regiongroupe_allclose();">
<img id="rgngroup_btn_close" src="$imgclose.png" title="すべて閉じる"/><span id="rgn_opener_closetxt">すべて閉じる</span>
</span>
EOD;
		return $button;

	}

	function buildDefaultbutton(){
		$button = 
<<<EOD

<table cellpadding='1' cellspacing='2'><tr>
<td valign='top'>
<!--
-->
	<span id='rgngroup_btn_open' style="cursor:pointer;border:gray 1px solid;"
	onclick="regiongroupe_allopen();">全て開く</span>
<!--
-->
</td></tr>
<tr>
<td valign='top'>
<!--
-->
	<span id='rgngroup_btn_close' style="cursor:pointer;border:gray 1px solid;"
	onclick="regiongroupe_allclose();">全て閉じる</span>
<!--
-->
</td>
</tr>
</table>

EOD;

		return $button;

	}

	function buildJavascript(){
		$curscript = 
<<<EOD
<script>

function regiongroupe_showtext(mode){

	if(mode){
		elms = document.querySelectorAll("#rgn_opener_opentxt");
		for(var i = 0; i < elms.length; i++) 
			elms[i].innerText="すべて開く";
		elms = document.querySelectorAll("#rgn_opener_closetxt");
		for(var i = 0; i < elms.length; i++) 
			elms[i].innerText="すべて閉じる";

/*
		document.getElementById('rgn_opener_opentxt').innerText="すべて開く";
		document.getElementById('rgn_opener_closetxt').innerText="すべて閉じる";
*/
	}else{
		elms = document.querySelectorAll("#rgn_opener_opentxt");
		for(var i = 0; i < elms.length; i++) 
			elms[i].innerText="";
		elms = document.querySelectorAll("#rgn_opener_closetxt");
		for(var i = 0; i < elms.length; i++) 
			elms[i].innerText="";
	}

}

function regiongroupe_region_allopen(){
	n=1;
	do{
		if(document.getElementById('rgn_summary'+n)==null){
			n= 0;		
		}else if(document.getElementById('rgn_summary'+n).style.display=='block'){
			document.getElementById('rgn_summary'+n).style.display='none';
			document.getElementById('rgn_summaryV'+n).style.display='block';
			document.getElementById('rgn_content'+n).style.display='block';
			n++;

		}else if(document.getElementById('rgn_summary'+n).style.display=='none'){
			n++;
		}
		else{
			n= 0;
		} 
	} while( n!= 0 );

}

function regiongroupe_divregion_allopen(){
	n=1;
	do{
		if(document.getElementById('drgn_summary'+n)==null){
			n= 0;		
		}else if(document.getElementById('drgn_summary'+n).style.display=='block'){
			document.getElementById('drgn_summary'+n).style.display='none';
			document.getElementById('drgn_summaryV'+n).style.display='block';
			document.getElementById('drgn_content'+n).style.display='block';
			n++;
		}else if(document.getElementById('drgn_summary'+n).style.display=='none'){
			n++;
		}
		else{
			n= 0;
		} 
	} while( n!= 0 );


}

function regiongroupe_region_allclose(){
	n=1;
	do{
		if(document.getElementById('rgn_summary'+n)==null){
			n= 0;		
		}else if(document.getElementById('rgn_summary'+n).style.display=='block'){
			n++;
		}else if(document.getElementById('rgn_summary'+n).style.display=='none'){
			document.getElementById('rgn_summary'+n).style.display='block';
			document.getElementById('rgn_summaryV'+n).style.display='none';
			document.getElementById('rgn_content'+n).style.display='none';
			n++;
		}
		else{
			n= 0;
		} 
	} while( n!= 0 )

}

function regiongroupe_divregion_allclose(){
	n=1;
	do{
		if(document.getElementById('drgn_summary'+n)==null){
			n= 0;		
		}else if(document.getElementById('drgn_summary'+n).style.display=='block'){
			n++;
		}else if(document.getElementById('drgn_summary'+n).style.display=='none'){
			document.getElementById('drgn_summary'+n).style.display='block';
			document.getElementById('drgn_summaryV'+n).style.display='none';
			document.getElementById('drgn_content'+n).style.display='none';
			n++;
		}
		else{
			n= 0;
		} 
	} while( n!= 0 );


}

function regiongroupe_allclose(){
	regiongroupe_region_allclose();
	regiongroupe_divregion_allclose();

}

function regiongroupe_allopen(){
	regiongroupe_region_allopen();
	regiongroupe_divregion_allopen();
}
</script>

EOD;
		

		return $curscript;

	}

} // end class RegiongroupPluginHTMLBuilder

?>
