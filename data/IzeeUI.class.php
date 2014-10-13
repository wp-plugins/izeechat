<?php


class IzeeUI {

	private $_url;
	private $_userInfos;

	/**
	 * Fonction routing
	 * 
	 * @param  string $name  Fonction que l'on souhaite appeler
	 * @author  Kevin B. Apizee Inc        
	 */
	public function newBox($name) {

		$this->_url = IzeeContainer::getRequestInst()->url;
		$this->_userInfos = IzeeContainer::getUserInst()->userInfos;
		$method = 'newBox'.ucfirst($name);
		return $this->$method();
	}

	/**
	 * Infos sur Apizee-IzeeChat
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function newBoxApizee() {
		return '
			<table class="table">
				<tr>
					<td style="color:#181818;">
						'.__("Live chat solution for your website's visitors", "Izeechat").' <br/><br/>'.__("IzeeChat is an instant messaging solution for real-time communication with your visitors. During your discussions, you can always switch to audio or video communication.", "Izeechat").'<br/>'.__("So, you can offer help and advice instantly to your visitors when they need it. With IzeeChat solution, you improve the conversion rate of visitors to buyers and improve your brand image by providing a service of real-time support to your visitors. Izeechat has many additional features.", "Izeechat").'
					</td>
				</tr>
				<tr>
					<td>
						<a target="_BLANK" class="btn bg-green btn-block" href="'.__("//apizee.com/izeechat", "Izeechat").'">'.__("More", "Izeechat").'</a>
					</td>
				</tr>
			</table>';
	}

	/**
	 * Methode pour le contact
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function newBoxContact() {
		return '
		<table class="table" style="margin-bottom: 0px;"">
		<tr><td style="color:#181818;">
			<span class="contact_info"><img src="'.plugins_url('includes/img/icon-address.png',ROOT_FILE).'" />'.__("4 Ampère street. 22300 Lannion, FRANCE.", "Izeechat").'</span>
			<span class="contact_info"><img src="'.plugins_url('includes/img/icon-phone.png',ROOT_FILE).'" />+33(0)2 30 96 61 35</span>
			<span class="contact_info"><img src="'.plugins_url('includes/img/icon-email.png',ROOT_FILE).'" /><a style="color:#00A65A;" target="_BLANK" href="mailto:info@apizee.com">info@apizee.com</a></span>
			<a target="_BLANK" class="btn bg-green btn-block" href="'.__("//www.apizee.com/contact-us/", "Izeechat").'" />'.__("Contact us", "Izeechat").'</a>
		</td></tr>
		</table>';
	}

	/**
	 * Authentification - Activation
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function newBoxAuth() {
		return '
		<div class="form-box" id="login-box">
			﻿<div class="header">'.__("Authentication", "Izeechat").'</div>
			<form method="post">
				<div class="body bg-gray">
					<div class="form-group">
						<input name="authentication_email" class="form-control" value="'.$this->_userInfos['email'].'" readonly type="email" required="required">
					</div>
					<div class="form-group">
						<input name="authentication_password" class="form-control" placeholder="'.__("Password", "Izeechat").'" type="password" required="required">
					</div>
				<input type="submit" name="authentication_submit" class="btn bg-orange btn-block" value="'.__("Connection", "Izeechat").'" />
				</div>
			</form>
			<div class="footer">
				<p><a href="//cloud.apizee.com/index.php/sfApply/resetPassword">'.__("Forgotten Password ?", "Izeechat").'</a></p>
				<p><a id="gotoregister" href="">&rarr; '.__("Create an Account", "Izeechat").'</a></p>
			</div>
		</div>
		<div class="form-box" id="register-box" style="display:none;">
			﻿<div class="header">'.__("Create an Account", "Izeechat").'</div>
			<form method="post">
				<div class="body bg-gray">
					<div class="form-group">
						<input name="register_lastname" class="form-control" size="30" placeholder="'.__("Lastname", "Izeechat").'" type="text" required="required">
					</div>
					<div class="form-group">
						<input name="register_firstname" class="form-control" size="30" placeholder="'.__("Firstname", "Izeechat").'" type="text" required="required">
					</div>
					<div class="form-group">
						<input name="register_email" class="form-control" size="60" type="email" required="required" readonly value="'.$this->_userInfos['email'].'">
					</div>
					<div class="form-group">
						<input name="register_password" class="form-control" placeholder="'.__("Password", "Izeechat").'" type="password" id="pass1" onkeyup="checkPass(); return false;" required="required">
					</div>
					<div class="form-group">
						<input name="register_password2" class="form-control" placeholder="'.__("Confirm Password", "Izeechat").'" type="password" id="pass2" onkeyup="checkPass(); return false;" required="required">
					</div>
					<input type="submit" name="register_submit" class="btn bg-orange btn-block" value="'.__("Register", "Izeechat").'" />
				</div>
				<div class="footer">
					<p><a id="gotologin" href="">&larr; '.__("Authentication", "Izeechat").'</a></p>
				</div>
			</form>	
		</div>
		';
	}

	/**
	 * Authentification Agent
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function newBoxAuthAgent() {
		return '
		<div class="form-box" id="login-box">
			﻿<div class="header">'.__("Authentication", "Izeechat").'</div>
			<form method="post">
				<div class="body bg-gray">
					<div class="form-group">
						<input name="login_email" class="form-control" readonly value="'.$this->_userInfos['email'].'" type="email" required="required">
					</div>
					<div class="form-group">
						<input name="login_password" class="form-control" placeholder="'.__("Password", "Izeechat").'" type="password" required="required">
						<input name="login_wpPassword" value="'.$this->_userInfos['password'].'" type="hidden" />
					</div>
				<input type="submit" name="login_submit" class="btn bg-orange btn-block" value="'.__("Connection", "Izeechat").'" />
				</div>
			</form>
			<div class="footer">
				<p><a href="//cloud.apizee.com/index.php/sfApply/resetPassword">'.__("Forgotten Password ?", "Izeechat").'</a></p>
			</div>
		</div>';
	}

	/**
	 * Lister les agents existant
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function newBoxListAgents() {
		return IzeeContainer::getUserInst()->listAgents($agents, $admins);
	}

	/**
	 * Formualire de création d'un nouvel agent wordpress
	 * 
	 * @author  Kevin B. Apizee Inc
	 */
	public function newBoxcreateAgent() {
		return IzeeContainer::getUtilsInst()->createAgent();
	}
}

?>