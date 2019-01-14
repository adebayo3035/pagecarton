<?php
/**
 * PageCarton Content Management System
 *
 * LICENSE
 *
 * @user   Ayoola
 * @package    Application_User_Creator
 * @copyright  Copyright (c) 2011-2016 PageCarton (http://www.pagecarton.com)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @version    $Id: Creator.php 10.14.2011 8.06 ayoola $
 */

/**
 * @see Ayoola_Abstract_Table
 */
 
require_once 'Ayoola/Abstract/Table.php';


/**
 * @user   Ayoola
 * @package    Application_User_Creator
 * @copyright  Copyright (c) 2011-2016 PageCarton (http://www.pagecarton.com)
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

class Application_User_Creator extends Application_User_Abstract 
{
	
    /**
     * Access level for player
     *
     * @var boolean
     */
	protected static $_accessLevel = array( 0 );
	
    /**
     * 
     * 
     * @var string 
     */
	protected static $_objectTitle = 'Create an account'; 
	
    /**	
     *
     * @var boolean
     */
	public static $editorViewDefaultToPreviewMode = true;
	
    /**
     * Default Database Table
     *
     * @var string
     */
	protected $_tableClass = 'Application_User';

    /**
     * This is the unique identifier of every user of the application
     * Only public users does not have a value for this. Signed in user must have an ID
     * That is the first thing that we acquire after a database insert.
     *
     * @var int
     */
	protected $_userId = null;

    /**
     * Sign in details
     *
     * @var array
     */
	protected $_userInfo;
	
    /**
     * Activation code for a new account created.
     *
     * @var int
     */
	protected $_activationCode = null;
	
	
    /**
     * The system email that is sent upon the completion of the account creation process
     * 
     *
     * @var array
     */
	protected $_activationEmail;

    /**
     * Do the Sign up process
     *
     * @param 
     * 
     */
    protected function init()
    {
		$this->createForm( $this->getParameter( 'submit_value' ) ?  : 'Create Account', $this->getParameter( 'legend' ) );
	//	$this->setViewContent( '<h2>Sign up for a free account.</h2>' ); 
		$auth = new Ayoola_Access();
		$urlToGo = $this->getParameter( 'return_url' ) ? : Ayoola_Page::getPreviousUrl();
		//	var_export( $urlToGo ); 
		Application_Javascript::header( Ayoola_Application::getUrlPrefix() . $urlToGo );
		$this->setViewContent( $this->getForm()->view() );
		if( ! $values = $this->getForm()->getValues() ){ return false; }

		$values['creation_time'] = time();
		if( empty( $values['password'] ) )
		{
			$values['password'] = Ayoola_Form::hashElementName( rand( 1000, 90999 ) );
		}
		if( empty( $values['username'] ) )
		{
			//	autogenerate username
			$values['username'] = null;
			if( ! empty( $values['firstname'] ) )
			{
				//	autogenerate username
				$values['username'] .= $values['firstname'];
			}
			if( ! empty( $values['lastname'] ) )
			{
				//	autogenerate username
				$values['username'] .= $values['lastname'];
			}
			
			$filter = new Ayoola_Filter_Username;
			$values['username'] = $filter->filter( $values['username'] );
			$validator = new Ayoola_Validator_DuplicateUser;
			$user = $values['username'];
			$i = 0;
			while( ! $validator->validate( $values['username'] ) )
			{

				if( $i > 10 )
				{
					$this->getForm()->setBadnews( 'Autogenerated username in use!' );
					$this->setViewContent( $this->getForm()->view(), true );
					return false;
				}
				$values['username'] = $user . ( ++$i );
			}

		}
	//	var_export( $values );	
		//	Save the user in the default user db table
		$userOptions = Application_Settings_Abstract::getSettings( 'UserAccount', 'user_options' );
		if( @$values['user_group'] )
		{
		//	var_export( $userOptions );
		//	var_export( $values );
		//	$database = 'cloud';
			if( is_array( $userOptions ) && ( in_array( 'allow_level_selection', $userOptions ) || in_array( 'allow_level_injection', $userOptions ) ) )
			{
				$authLevel = new Ayoola_Access_AuthLevel;
				$authLevel = $authLevel->selectOne( null, array( 'auth_level' => $values['user_group'] ) );
			//	$options = array();
		//		foreach( $authLevel as $each )
				{
					if( is_array( $authLevel['auth_options'] ) && in_array( 'allow_signup', $authLevel['auth_options'] ) && $values['user_group'] != 99  )
					{
					//	var_export( $authLevel );
						$values['access_level'] = $values['user_group'];  
					}
				}
			}
		//	var_export( $values );

		}

		if( empty( $values['access_level'] ) )
		{
			$values['access_level'] = 1;
		}

		if( ! $database = Application_Settings_Abstract::getSettings( 'UserAccount', 'default-database' ) )
		{
			$database = 'file';
		}
		$saved = false;
		$message = null;
		switch( $database )
		{
			case 'cloud':
				//	If this is our first signup after we install, we could be made a super user
				$response = Ayoola_Api_SignUp::send( $values );
			//	var_export( $response );
				if( is_array( $response ) )
				{
					$saved = true;
				}
				else
				{
					$this->getForm()->setBadnews( $response );
				}
				
				//	Notify user that we are hosting users on the cloud
			//	$message = 'You may recieve an e-mail from <a href="http://account.ayoo.la/">Ayoo.la Accounts</a>, the provider of our Application User Account system.';
			break;
			case 'relational':
				if( $this->_db() )
				{ 
					$saved = true;
				}
				
				//	Send Verification E-mail
				Application_User_Verify_Email::resetVerificationCode( $values );
			break;
			case 'file':
			//	var_export( $values );
				try
				{
					unset( $values['password2'] );
					//	Retrieve the password hash
					$access = new Ayoola_Access();
					$hashedCredentials = $access->hashCredentials( $values );
				//	$values = $hashedCredentials + $values; 	//	We need raw passwords later for login
					Ayoola_Access_Localize::info( $hashedCredentials + $values );
				}
				catch( Exception $e )
				{
				//	var_export( $e->getMessage() );
				//	var_export( $e->getTraceAsString() );
				}
			//	var_export( $values );
				$saved = true;
 				
				//	Send Verification E-mail
				//	not yet working for flat files
			//	Application_User_Verify_Email::resetVerificationCode( $values );
			break;
		
		}
	//	var_export( $saved );
		if( ! $saved )
		{ 
			$this->setViewContent( $this->getForm()->view(), true );
			return false;
		}
 		$this->setViewContent( '<h2 class="goodnews">Account Confirmation</h2>', true );
 		$this->setViewContent( '<p>New user account has been created successfully. An email has been sent to ' . $values['email'] . ' containing how to activate and verify the new account. You can login immediately.</p>' );
 		$this->setViewContent( '<h4></h4>' );
		
		if( ! Ayoola_Application::isClassPlayer() )
		{
 			$this->setViewContent( '<p><a target="_parent" class="pc-btn" href="' . Ayoola_Application::getUrlPrefix() . Ayoola_Page::getPreviousUrl( '/account' ) . '">Continue...</a></p>' );     
		}


		//	Auto log me in now without confirmation
		if( $this->getParameter( 'signin' ) || ! empty( $urlToGo ) ) 
		{
	//		var_export( $values );  
			if( ! $loginResponse = Ayoola_Access_Login::localLogin( $values ) )   
			{
	//		var_export( $loginResponse );  
				$loginResponse = Ayoola_Access_Login::apiLogin( $values );
			}
	//		var_export( $values );  
	//		var_export( $loginResponse );  
	//		exit();
			
			if( $urlToGo && ! Ayoola_Application::isXmlHttpRequest() && ! $this->getParameter( 'no_redirect' )  )
			{
				header( 'Location: ' . Ayoola_Application::getUrlPrefix() . $urlToGo );
				exit();
			}
			$this->setViewContent( '<div id="ayoola-js-redirect-whole-page"></div>' );
		}
		
		if( ! @$this->_sendActivationEmail() )
		{ 
			$this->setViewContent( '<p class="badnews">We were unable to deliver the email to you due to system error</p>' ); 
		}
		
		//	Referrers
		do 
		{
			if( empty( $_COOKIE['pc_referrer'] ) && empty( $_REQUEST['pc_referrer'] ) )
			{
				break;
			}
			@$referrer = $_REQUEST['pc_referrer'] ? : $_COOKIE['pc_referrer'];
		//	$userInfo = Ayoola_Application::getUserInfo();		
			if( ! $userInfo = Ayoola_Access::getAccessInformation( $referrer ) )
			{
				break;
			}
				
			$table = Application_User_Referral::getInstance();
			if( $table->insert( array( 'referrer' => strtolower( $userInfo['username'] ), 'referral' => strtolower( $values['username'] ), 'r_time' => time() ) ) )
			{
				self::sendMail( array( 'body' => 'Good job! @' . $values['username'] . ' just used your referral link to sign up!', 'subject' => 'New Referral', 'to' => $userInfo['email'], ) );
			}
		}
		while( false );
		
    }

    /**
     * This method sets the email variable to a value
     *
     * @param array
     * @return void
     */
    protected function _setActivationEmail( $email )
    {
		$this->_activationEmail = $email;
    } 
	
    /**
     * Retrieves the email from the systems email db table
     *
     * @param void
     * @return void | null
     */
    protected function _getActivationEmail()
    {
		
        $db = new Application_User_NotificationMessage;
		$email = $db->selectOne( null, array( 'subject' =>  'New Account Opened' ) );
		//	var_export( $email );

		if( ! $email )
		{
			//$this->_badnews[] = __CLASS__ . ' - Email cannot be retrieved ';
			return false;
		}
		$values = $this->getForm()->getValues();
		
		$domain = Ayoola_Page::getDefaultDomain();
		$valuesForReplace = $values;
		$valuesForReplace['domain'] = $domain;
		$email['to'] = $values['email'];
		$email['from']  = "From: \"{$domain}\" <accounts@{$domain}>\r\n";
	//	var_export( $email );
		$email['body'] = self::replacePlaceholders( $email['body'], $valuesForReplace );
	//	var_export( $values );
	//	var_export( $email );
		$this->_setActivationEmail( $email );
		return true;
    } 
	
    /**
     * Returns the email
     *
     * @param void
     * @return array
     */
    public function getActivationEmail()
    {
		if( is_null( $this->_activationEmail ) ){ $this->_getActivationEmail(); }
		return $this->_activationEmail;
    } 
	
    /**
     * Internal email sending method
     *
     * @param void
     * @return boolean
     */
    protected function _sendActivationEmail()
    {
		$email = $this->getActivationEmail();
		if( empty( $email['body'] ) ){ return false; }

		$header = 	$email['from'] . "X-Mailer: PHP/" . phpversion() ;
		$sent = mail( $email['to'], $email['subject'], $email['body'], $header );
		//var_export( $email['to'] );
		if( ! $sent ){ return false; }
		return true;
    } 
	
    /**
     * This method does the database operation
     *
     * @param void
     * @return boolean
     */
    protected function _db()
    {
        if( ! $this->_validate() )
		return false;
		
		$values = $this->getForm()->getValues();
	//	var_export( $values );
		
		// I'm first going to look for duplicate entries for username
		$select = $this->getDbTable()->selectOne( '', 'useremail', " username ='{$values['username']}' " );
		if( $select )
		{
			$this->getForm()->setBadnews( 'Someone else has already choosen ' . $values['username'] );
			return false;
		}
	//	var_export( $values );
		
		// I'm first going to look for duplicate entries for email
		$select = $this->getDbTable()->selectOne( '', 'useremail', " email = '{$values['email']}' " );
		
		if( $select )
		{
			$this->getForm()->setBadnews( 'The email is already used on another account.' );
			return false;
		}
		
		$inserted = $this->getDbTable()->insert( $values ); // stage 1 - insert username
		
		if( ! $inserted )
		{
			$this->getForm()->setBadnews( 'System error from - ' . $this->getObjectName() );
			return false;
		}
		
		$this->_userId = (int) $this->getDbTable()->insertId(); // stage 1 - get user_id
		$this->insertId = $this->_userId;
		
		$values2 = array( 'user_id' => $this->_userId, 'code' => $this->getActivationCode() ); // Input the server values and constants
		
		$values = array_merge( $values, $values2);
		
		require_once 'Ayoola/Filter/Hash.php';
		$filter = new Ayoola_Filter_Hash( 'sha512' );
		$values['password'] = $filter->filter( $values['password'] );
		unset( $values2 );
		$this->_userInfo = $values; // Save Values for later use.
		
		$tables = array();
		$tables[] = new Application_User_UserEmail; // stage 2 - email table
		$tables[] = new Application_User_UserPassword; // stage 3 - password
		$tables[] = new Application_User_UserPersonalInfo; // stage 4 - Personal info
		$tables[] = new Application_User_UserActivation; // stage 5 - Activation
	//	$tables[] = new Application_User_UserSettings; // stage 6 - Settings
		//$tables[] = new Application_User_UserAction; // stage 7 - Log
		
		foreach( $tables as $table )
		{
			$inserted = $table->insert( $values );
			if( ! $inserted )
			{
				$this->getForm()->setBadnews( 'System error from - ' . $this->getObjectName() );
				return false;
			}
		}
		unset( $tables, $table, $inserted, $values ); // free up resources
		return true;
    } 
	
    /**
     * Retrieves the activation code
     *
     * @param void
     * @return int
     */
    public function getActivationCode()
    {
		if( is_null( $this->_activationCode ) )
		{ 
			$this->_activationCode = rand( 234803000, 234809000 );
		}
		return $this->_activationCode; 
    } 
	// END OF CLASS
}
