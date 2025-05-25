<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: regiongroup.inc.php,v 1.1 2025  Tomose
// Based on region.inc.php,v 1.2 2005/01/22 15:50:00 xxxxx Exp $
//
// ���낢��l�������ʁA���{�I�Ȍ��������s���B
// �EJavascript�����̂ŁA1�񂵂��Ăяo���Ȃ�����������̂͑Ó��B
// �E�����u�J���v�u����v�{�^����1�����ł������Ȃ��̂͂��������B
//
// usage
// #regiongroup(mode)
//	mode: �{�^���\���̎d�l
//		���w�� or default: Table�`���̃^�b�`�G���A�i�ߋ��݊�����j
//		image: �A�C�R���\���E�c�u�����[�h
//				�u[+]���ׂĊJ���v�Ɓu[-]���ׂĕ���v�Ƃ����w�A�C�R��+�����x�\��
//				�����̂Q���c�ɕ���ł���`
//		image_vertical : image �w��Ɠ�������
//		image_horizontal : �w�A�C�R��+�����x�\���B
//				�u�J���v�Ɓu����v�����ɕ��ׂĂ���
//
// �{�v���O�C�����C���X�g�[������ꍇ�Apukiwiki/image�f�B���N�g���Ɏ��̂Q��
// �摜�t�@�C����u���Ă��������i������̉摜�͗F������͔z�z���Ă��܂���j
//		ExpandAll.png :	�u���ׂĊJ���v�A�C�R��
//		CollapseAll : 	�u���ׂĕ���v�A�C�R��
// ������̉摜�ɂ��ẮA�F���͔z�z���Ă��܂���B�e���ŏ������������B


function plugin_regiongroup_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new RegiongroupPluginHTMLBuilder();
	
	//���܂ł͉�ʕ\���́utable�`���ł̃N���b�N�G���A�Œ�v���������A
	//���̎�i�ł��悢�`�ɂ���B
	// ���̏������p�����[�^�Ƃ��Ď󂯎���悤�ɂ���B
	// �݊����̂��߁A�ŏ��̃p�����[�^�͋󔒂����e�B
	$args = func_get_args();
	$prm= array();

	$prm['mode']=array_shift($args);
	if($prm['mode']=='') $prm['mode']='default';

	foreach( $args as $key => $arg){
		tomoseDBG("key:".$key);			
		tomoseDBG("arg:".$arg);			
		if($key==0){
			//���p�����[�^�B�󗓂Ȃ�]���ʂ�A����ȊO�Ȃ�ʓ���B
			$prm['mode']=$arg;
			if($arg=='') $prm['mode']='default';
			
		}else	if(strpos($arg,'=')){
			// prmname = xxx �`���̈����B
			$argss= explode('=',$arg);
			$argss = array_map('trim', $argss);
			switch ($argss[0])	{
				case 'prmname':
					// $argss[1]�̒l�ŏ���
					break;
				default:
					break;

			}
		}
		else{
			//prmname���B$key�̈ʒu�ŏ������邵���Ȃ��B
			switch ($key)
			{
				default:
					break;

			}

		}
		
	}

	// �g�s�l�k�ԋp
	return $builder->build($prm['mode']);
}


// �N���X�̍�����http://php.s3.to/man/language.oop.object-comparison-php4.html
class RegiongroupPluginHTMLBuilder
{
	var $description;
	var $isopened;
	var $scriptVarName;
	var $id_buttonopen,$id_buttonclose;


	//�� build���\�b�h���Ă񂾉񐔂��J�E���g����B
	// �P��HTML�ɂ� Javascript�� 1�񂾂������o�͂����Ȃ����߂̏����B
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

	// convert_html()���g���āA�T�v�̕����Ƀu�����P�b�g�l�[�����g����悤�ɉ��ǁB
	function setDescription($description){ $this->description = "aaa"; }

	function build($mode){
		//�Ăяo���ꂽ�񐔂��J�E���g�B
		$this->callcount++;
		$html = array();
		// �ȍ~�A�g�s�l�k�쐬����

		array_push( $html, $this->buildButtonHtml($mode) );

		return join($html);
		
	}

	// �� �{�^���̕����B
	function buildButtonHtml($mode){
		switch ($mode)	{
			// $argss[1]�̒l�ŏ���
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

		//����Ăяo�����̂݁AJavascript��ǉ��œf���o���B
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
<img id='rgngroup_btn_open' src='$imgopen.png' title='���ׂĊJ��' />
<span id='rgn_opener_opentxt'>���ׂĊJ��</span>
</span>
EOD;
		return $button;

	}

	function buildCloseImagebutton(){
		$imgclose = IMAGE_DIR."CollapseAll";
		$button = 
<<<EOD
<span style="cursor:pointer;" onclick="regiongroupe_allclose();">
<img id="rgngroup_btn_close" src="$imgclose.png" title="���ׂĕ���"/><span id="rgn_opener_closetxt">���ׂĕ���</span>
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
	onclick="regiongroupe_allopen();">�S�ĊJ��</span>
<!--
-->
</td></tr>
<tr>
<td valign='top'>
<!--
-->
	<span id='rgngroup_btn_close' style="cursor:pointer;border:gray 1px solid;"
	onclick="regiongroupe_allclose();">�S�ĕ���</span>
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
			elms[i].innerText="���ׂĊJ��";
		elms = document.querySelectorAll("#rgn_opener_closetxt");
		for(var i = 0; i < elms.length; i++) 
			elms[i].innerText="���ׂĕ���";

/*
		document.getElementById('rgn_opener_opentxt').innerText="���ׂĊJ��";
		document.getElementById('rgn_opener_closetxt').innerText="���ׂĕ���";
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
