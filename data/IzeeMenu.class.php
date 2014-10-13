<?php

class IzeeMenu {

	/**
	 * Vue pour activer le plugin
	 * 
	 * @return stdio HTML
	 * @author  Kevin B. Apizee Inc
	 */
	public function activationView() {
		echo IzeeContainer::getUtilsInst()->showError();
		IzeeContainer::getUtilsInst()->getheader();
		$UI = IzeeContainer::getUiInst();

		echo $UI->newBox('auth');

		IzeeContainer::getUtilsInst()->getFooter();
	}

	/**
	 * Vue pour l'admin et les agents
	 * 
	 * @return stdio HTML
	 * @author  Kevin B. Apizee Inc
	 */
	public function adminView() {
		echo IzeeContainer::getUtilsInst()->showError();
		IzeeContainer::getUtilsInst()->getheader();
		$UI = IzeeContainer::getUiInst();
		$config     = IzeeContainer::getCoreInst()->config;

		$userInfos  = IzeeContainer::getUserInst()->userInfos;
		$agentInfos = IzeeContainer::getUserInst()->agentInfos[1][0];

		echo '
		<section class="content">
    		<div class="row">
        		<div class="col-lg-5">
					<div class="box box-primary">
						<div class="box-header">
                        	<h2 class="box-title">'.__("Agents Listing", "Izeechat").'</h2>
                    	</div>
                    	<div class="box-body">
                    		<div class="agents-list">
                    			'.$UI->newBox('listAgents').'
                    		</div>
                    	</div>
					</div>
        		</div>
        		<div class="col-lg-3">
        			<div class="box box-warning">
	        			<div class="box-header">
	                    	<h2 class="box-title">'.__("Create an Agent", "Izeechat").'</h2>
	                	</div>
	                	<div class="box-body">
	                		<div class="agent-form">
	        					'.$UI->newBox('createAgent').'
	        				</div>
	        			</div>
	        		</div>
        		</div>

				<div class="col-lg-4">	
	        		<div class="col-lg-12" style="padding: 0;">
						<div class="box box-primary">
							<div class="box-header">
	                        	<h2 class="box-title">'.__("Dashboard Apizee", "Izeechat").'</h2>
	                    	</div>
	                    	<div class="box-body">
	                    		<div class="contact_apizee">
	                    			<a class="btn bg-blue btn-block" href="'.$config['api']['host'].'/index.php/dashboard?layout=true" title="'.__('Link to the the cloud\'s dashboard', 'Izeechat').'" target="_BLANK">
				                    	'.__("Open in other tab", "Izeechat").'
									</a>
	                    			<a class="btn bg-blue btn-block fancybox fancybox.iframe" data-fancybox-type="iframe" href="'.$config['api']['host'].'/index.php/api/login?username='.$userInfos['email'].'&password='.$agentInfos['cloud_password'].'&layout=false">
										'.__("Open here", "Izeechat").'
									</a>
	                    		</div>
	                    	</div>
						</div>
	        		</div>
	        		<div class="col-lg-12" style="padding: 0;">
						<div class="box box-warning">
							<div class="box-header">
	                        	<h2 class="box-title">'.__("Support", "Izeechat").'</h2>
	                    	</div>
	                    	<div class="box-body">
	                    		<div class="support_apizee">
	                    			<a target="_BLANK" class="btn bg-orange btn-block" href="'.__("//doc.apizee.com/izeechat-on-wordpress/", "Izeechat").'" />'.__("Documentation", "Izeechat").'</a>
	                    		</div>
	                    	</div>
						</div>
        			</div>
        		</div>


        	</div>
        	<div class="row">
        		<div class="col-lg-6">
        			<div class="box box-success">
	        			<div class="box-header">
	                    	<h2 class="box-title">'.__("About IzeeChat", "Izeechat").'</h2>
	                	</div>
	                	<div class="box-body" style="padding: 0;">
	                		<div class="about-izeechat">
	        					'.$UI->newBox('apizee').'
	        				</div>
	        			</div>
	        		</div>
        		</div>
        		<div class="col-lg-6">
					<div class="box box-success">
						<div class="box-header">
                        	<h2 class="box-title">'.__("Contact", "Izeechat").'</h2>
                    	</div>
                    	<div class="box-body">
                    		<div class="contact_apizee">
                    			'.$UI->newBox('contact').'
                    		</div>
                    	</div>
					</div>
        		</div>		
        	</div>
        </section>';

		IzeeContainer::getUtilsInst()->getFooter();
	}

	/**
	 * Vue pour activer les agents non authentifiés
	 * 
	 * @return stdio HTML
	 * @author  Kevin B. Apizee Inc
	 */
	public function userView() {
		echo IzeeContainer::getUtilsInst()->showError();
		IzeeContainer::getUtilsInst()->getheader();
		$UI = IzeeContainer::getUiInst();
		echo $UI->newBox('authAgent');

		IzeeContainer::getUtilsInst()->getFooter();
	}
}

?>