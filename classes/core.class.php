<?php

defined( '_ACM_VALID' ) or die( 'Direct Access to this location is not allowed.' );



class core {

	function core() {
		$this->account = ACCOUNT::load();
		$this->secure_post();
	}

	function index() {
		if($this->account->is_logged())
			$this->show_account();
		else
			$this->show_login();
	}

	function loggout() {
		global $vm;
		$this->account->loggout();
		MSG::add_valid($vm['_logout']);
		$this->index();
	}

	function login() {
		global $vm;

		if(empty($_POST['Luser']) || empty($_POST['Lpwd']))
		{
			MSG::add_error($vm['_no_id_no_pwd']);
		}else{

			$this->secure_post();

			if(!$this->account->auth($_POST['Luser'], $_POST['Lpwd'], @$_POST['Limage']))
				MSG::add_error($vm['_wrong_auth']);
		}

		$this->index();
	}

	function show_login() {
		global $template, $vm, $id_limit, $pwd_limit, $act_img;
		$template->assign('vm', array(
		    'exist_account'		=> $vm['_exist_account'],
		    'account_length'		=> $id_limit,
		    'password_length'	=> $pwd_limit,
		    'account'			=> $vm['_account'],
		    'password'			=> $vm['_password'],
		    'login_button'		=> $vm['_login_button'],
		    'forgot_password'	=> $vm['_forgot_password'],
		    'new_account'		=> $vm['_new_account'],
		    'new_account_text'	=> $vm['_new_account_text'],
		    'create_button'		=> $vm['_create_button']
		));
		if($act_img) {
			$template->assign('image', 'image');
		}
		$template->display('form.tpl');
	}

	function show_account() {
		global $template, $vm;
		
		$template->assign('vm', array(
			'title_page'		=> $vm['_title_page'],
		    'account_text'		=> $vm['_chg_pwd_text']
		));
		
		$modules = array();
		
		$modules[] = array('name'=>$vm['_chg_pwd'], 'link'=>'?action=show_chg_pwd');
		
		if ($this->allow_char_mod())
			$modules[] = array('name'=>$vm['_accounts_services'], 'link'=>'?action=acc_serv');
		
		if ($this->account->can_chg_email())
			$modules[] = array('name'=>$vm['_chg_email'], 'link'=>'?action=show_chg_email');
		
		$modules[] = array('name'=>$vm['_logout_link'], 'link'=>'?action=loggout');
		
		$template->assign('modules', $modules);
		
		$template->register_block('dynamic', 'smarty_block_dynamic', false);
		$template->display('account.tpl');
	}

	function registration() {
		global $vm;

		if($this->account->create($_POST['Luser'], $_POST['Lpwd'], $_POST['Lpwd2'], $_POST['Lemail'], @$_POST['Limage'])) {
			$this->show_login();
		}else{
			$this->show_create(true);
		}
	}

	function show_ack(){
		global $template,$vm;
		$template->assign('vm', array(
		    'terms_and_condition'		=> $vm['_TERMS_AND_CONDITION'],
		    'return'					=> $vm['_return'],
		    'accept_button'				=> $vm['_accept_button']
		));
		$template->display('ack.tpl');
	}

	function show_create($acka = false) {
		global $template, $vm, $act_img, $id_limit, $pwd_limit,$ack_cond;

		$ack = (@$_POST['ack'] == 'ack') ? true : false;
		$ack = ($acka) ? true : $ack;

		if($ack_cond && !$ack) {
			$this->show_ack();
			return false;
		}
		
		$template->assign('vm', array(
		    'new_account'			=> $vm['_new_account'],
		    'new_account_text'		=> $vm['_new_account_text2'],
		    'account_length'		=> $id_limit,
		    'password_length'		=> $pwd_limit,
		    'account'				=> $vm['_account'],
		    'password'				=> $vm['_password'],
		    'password2'				=> $vm['_password2'],
		    'email'					=> $vm['_email'],
		    'image_control_desc'	=> $vm['_image_control_desc'],
		    'return'				=> $vm['_return'],
		    'create_button'			=> $vm['_create_button'],
		    'post_id'				=> @$_POST['Luser'],
		    'post_email'			=> @$_POST['Lemail']
		));
		if($act_img) {
			$template->assign('image', 'image');
		}
		$template->display('create.tpl');
	}

	function show_forget() {
		global $template, $vm, $act_img, $id_limit;
		$template->assign('vm', array(
		    'forgot_pwd'			=> $vm['_forgot_pwd'],
		    'forgot_pwd_text'		=> $vm['_forgot_pwd_text'],
		    'account_length'		=> $id_limit,
		    'account'				=> $vm['_account'],
		    'email'					=> $vm['_email'],
		    'image_control_desc'	=> $vm['_image_control_desc'],
		    'return'				=> $vm['_return'],
		    'forgot_button'			=> $vm['_forgot_button'],
		    'post_id'				=> @$_POST['Luser'],
		    'post_email'			=> @$_POST['Lemail']
		));
		if($act_img) {
			$template->assign('image', 'images');
		}
		$template->display('forgot_pwd.tpl');
	}

	function forgot_pwd() {
		global $vm;

		if($this->account->forgot_pwd($_POST['Luser'], $_POST['Lemail'], @$_POST['Limage'])) {
			MSG::add_valid($vm['_password_request']);
			$this->index();
		}else{
			$this->show_forget();
		}

		return true;
	}

	function forgot_pwd_email() {
		global $vm;

		if($this->account->forgot_pwd2($_GET['login'], $_GET['key'])) {
			MSG::add_valid($vm['_password_reseted']);
			$this->index();
		}else{
			MSG::add_error($vm['_control']);
			$this->show_forget();
		}

		return true;
	}

	function chg_pwd_form() {
		global $vm;

		if(!$this->account->verif()) {
			MSG::add_error($vm['_WARN_NOT_LOGGED']);
			$this->index();
			return;
		}

		$account = unserialize($_SESSION['acm']);

		if($this->account->edit_password($_POST['Lpwdold'], $_POST['Lpwd'], $_POST['Lpwd2'])) {
			MSG::add_valid($vm['_change_pwd_valid']);
			$this->show_account();
		}
		else
		{
			$this->show_chg_pwd();
		}
	}

	function show_chg_pwd() {
		global $vm;
		
		if(!$this->account->verif()) {
			MSG::add_error($vm['_WARN_NOT_LOGGED']);
			$this->index();
			return;
		}

		global $template, $pwd_limit;

		$template->assign('vm', array(
		    'chg_pwd'				=> $vm['_chg_pwd'],
		    'chg_pwd_text'			=> $vm['_chg_pwd_text'],
		    'password_length'		=> $pwd_limit,
		    'passwordold'			=> $vm['_passwordold'],
		    'password'				=> $vm['_password'],
		    'password2'				=> $vm['_password2'],
		    'return'				=> $vm['_return'],
		    'chg_button'			=> $vm['_chg_button']
		));
		
		$template->display('chg_pwd.tpl');
	}

	function chg_email_form() {
		global $vm;

		if(!$this->account->verif()) {
			MSG::add_error($vm['_WARN_NOT_LOGGED']);
			$this->index();
			return;
		}

		if(!$this->account->can_chg_email()) {
			$this->index();
			return;
		}

		$this->account = unserialize($_SESSION['acm']);

		if($this->account->edit_email($_POST['Lpwd'], $_POST['Lemail'], $_POST['Lemail2'])) {
			MSG::add_valid($vm['_change_email_valid']);
			$this->show_account();
		}
		else
		{
			$this->show_chg_email();
		}
	}

	function show_chg_email() {
		global $vm, $can_chg_email;
		
		if(!$this->account->verif()) {
			MSG::add_error($vm['_WARN_NOT_LOGGED']);
			$this->index();
			return;
		}

		if(!$this->account->can_chg_email()) {
			$this->index();
			return;
		}

		global $template, $pwd_limit;
		
		$template->assign('vm', array(
		    'chg_pwd'				=> $vm['_chg_email'],
		    'chg_pwd_text'			=> $vm['_chg_email_text'],
		    'password_length'		=> $pwd_limit,
		    'password'				=> $vm['_password'],
		    'email'					=> $vm['_email'],
		    'email2'				=> $vm['_email2'],
		    'return'				=> $vm['_return'],
		    'chg_button'			=> $vm['_chg_button']
		));
		
		$template->display('chg_email.tpl');

	}

	function email_validation() {
		global $vm;

		if($this->account->email_validation($_GET['login'], $_GET['key'])) {
			MSG::add_valid($vm['_email_activated']);
		}else{
			MSG::add_error($vm['_control']);
		}
		
		$this->index();

		return true;
	}
	
	function acc_serv(){
		global $vm;
		if(!$this->allow_char_mod()) {
			MSG::add_error($vm['_acc_serv_off']);
			$this->index();
			return;
		}
				
		global $template, $accserv;
		
		$template->assign('vm', array(
			'select_item'			=> $vm['_accounts_services'],
			'return'				=> $vm['_return'],
		));
		
		$items = array();
		
		if($accserv['allow_fix'])
			$items[] = array('id' => 0, 'name' => $vm['_character_fix'], 'link' => '?action=char_fix_l');
		
		if($accserv['allow_unstuck'])
			$items[] = array('id' => 1, 'name' => $vm['_character_unstuck'], 'link' => '?action=char_unstuck_l');
		
		if($accserv['allow_sex'])
			$items[] = array('id' => 1, 'name' => $vm['_character_sex'], 'link' => '?action=char_sex_l');
		
		if($accserv['allow_name'])
			$items[] = array('id' => 1, 'name' => $vm['_character_name'], 'link' => '?action=char_name_l');
		
		$template->assign('items', $items);
		
		$template->register_block('dynamic', 'smarty_block_dynamic', false);
		
		$template->display('select.tpl');
	}
	
	function char_ufl($mod = null){
		
		global $accserv, $vm;
		
		if(is_null($mod)) {$this->index(); return;}
		
		if(!$this->allow_char_mod() || !$accserv['allow_'.$mod]) {
			MSG::add_error($vm['_acc_serv_off']);
			$this->index();
			return;
		}
		
		global $template;
		
		unset($worlds);
		$worlds = WORLD::load_worlds(); // charging world
		
		$template->assign('vm', array(
			'select_item'			=> $vm['_character_'.$mod],
			'select_desc'			=> $vm['_character_'.$mod.'_desc'],
		    'return'				=> $vm['_return']
		));
		
		$items = array();
		foreach  ($worlds as $world) {
			foreach  ($world->get_chars() as $char) {
				$items[] = array('id' => $world->get_id(), 'name' => $world->get_name() . ' : ' .$char->getName(), 'link' => '?action=char_'.$mod.'&wid='.$world->get_id().'&cid='.$char->getId());
			}
		}
		
		$template->assign('items', $items);
		
		$template->register_block('dynamic', 'smarty_block_dynamic', false);
		
		$template->display('select.tpl');
	}
	
	function char_unstuck_l() {
		$this->char_ufl('unstuck');
	}
	
	function char_fix_l() {
		$this->char_ufl('fix');
	}
	
	function char_sex_l() {
		$this->char_ufl('sex');
	}
	
	function char_name_l() {
		$this->char_ufl('name');
	}
	
	function char_uf($mod = null) {
		
		if(is_null($mod)) {$this->index(); return;}

		global $accserv, $vm;
		
		if(!$this->allow_char_mod() and !$accserv['allow_'.$mod]) {
			MSG::add_error($vm['_acc_serv_off']);
			$this->index();
			return;
		}
		
		if(empty($_GET['wid']) || empty($_GET['cid'])) {
			MSG::add_error($vm['_error_select_char']);
			$this->index();
			return;
		}
		
		$char = new character($_GET['cid'], $_GET['wid']);
		
		if(is_null($char->getId())) {
			MSG::add_error($vm['_error_select_char']);
			$this->index();
			return;
		}
		
		global $template, $vm;
		
		$template->assign('vm', array(
			'select_item'	=> $vm['_character_'.$mod],
			'select_desc'	=> sprintf($vm['_character_'.$mod.'_confirm'], $char->getName(), world::get_name_world($char->getWorldId()), $vm['_character_sex_'.$char->getGender()], $vm['_character_sex_'.((int)(!$char->getGender()))]),
		    'return'		=> $vm['_return']
		));
		
		$items = array();
		$items[] = array('id' => 1, 'name' => $vm['_confirm'], 'link' => '?action=char_'.$mod.'_confirm&wid='.$char->getWorldId().'&cid='.$char->getId());
		$items[] = array('id' => 1, 'name' => $vm['_back'], 'link' => '?action=char_'.$mod.'_l');
		$template->assign('items', $items);
		
		$template->register_block('dynamic', 'smarty_block_dynamic', false);
		$template->display('select.tpl');
	}
	
	function char_unstuck() {
		$this->char_uf('unstuck');
	}
	
	function char_fix() {
		$this->char_uf('fix');
	}
	
	function char_sex() {
		$this->char_uf('sex');
	}
	
	function char_name() {
		$this->char_uf('name');
	}

	function char_ufc($mod = null) {
		
		if(is_null($mod)) {$this->index(); return;}
		
		global $accserv, $vm;
		
		if(!$this->allow_char_mod() or !$accserv['allow_'.$mod]) {
			MSG::add_error($vm['_acc_serv_off']);
			$this->index();
			return;
		}
		
		if(empty($_GET['wid']) || empty($_GET['cid'])) {
			MSG::add_error($vm['_error_select_char']);
			$this->index();
			return;
		}
		
		$char = new character($_GET['cid'], $_GET['wid']);

		if(!$char->$mod())
			MSG::add_error($vm['_character_'.$mod.'_no']);
		else
			MSG::add_valid($vm['_character_'.$mod.'_yes']);

		$this->index();

		return;
	}
	
	function char_unstuck_confirm() {
		$this->char_ufc('unstuck');
	}
	
	function char_fix_confirm() {
		$this->char_ufc('fix');
	}
	
	function char_sex_confirm() {
		$this->char_ufc('sex');
	}
	
	function char_name_confirm() {
		$this->char_ufc('name');
	}

	function activation() {
		global $vm;

		if(!$this->account->valid_account(htmlentities($_GET['key'])))
			MSG::add_error($vm['_activation_control']);
		else
			MSG::add_valid($vm['_account_actived']);

		$this->index();

		return;
	}
	
	function allow_char_mod() {
		global $accserv;
		
		$accserv['allow_name'] = false;
		
		if(!$accserv['allow_char_mod'])
			return false;
		
		if(!$accserv['allow_fix'] && !$accserv['allow_unstuck'] && !$accserv['allow_name'] && !$accserv['allow_sex'])
			return false;
		
		return true;
	}

	protected function secure_post() {
		global $id_limit, $pwd_limit;

		if (!$_POST) return;

		$_POST = array_map('htmlentities', $_POST);
		$_POST = array_map('htmlspecialchars', $_POST);

		foreach($_POST as $key => $value) {
			if ($key == 'Luser')
				$_POST[$key] = substr($value, 0, $id_limit);

			if ($key == 'Lpwd')
				$_POST[$key] = substr($value, 0, $id_limit);
		}
		
		return;
	}

	function gen_img_cle($num = 5) {
		$key = '';
		$chaine = "ABCDEF123456789";
		for ($i=0;$i<$num;$i++) $key.= $chaine[rand()%strlen($chaine)];
		$_SESSION['code'] = $key;
	}
}
?>