<?php
class Feul
{
	/** 
	 * A public variable 
	 * 
	 * @var object stores database connection
	*/  
	public $dbh;

	//The below variables are information from the Feul xml file
	public $Storage;
	public $LoginCSS;
	public $WelcomeCSS;
	public $RegisterCSS;
	public $ProtectedMessage;
	public $DB_Host;
	public $DB_User;
	public $DB_Pass;
	public $DB_Name;
	public $DB_Table_Name;
	public $Errors;
	
	/** 
     * Sets Creates settings xml file if it is not already created
     * 
     * @return void 
     */  
	public function __construct()
	{

		//check to see if the FeulFile file is created
		//If not created -> create
		if(!file_exists(FeulFile))
		{
			global $SITEURL;
			
			$this->Storage = 'XML';
			$this->Email = "noreply@".$_SERVER['HTTP_HOST'];
			$this->DB_Host = '';
			$this->DB_User = '';
			$this->DB_Pass = '';
			$this->DB_Name = '';
			$this->DB_Table_Name = '';
			$this->Errors = 'Off';

			//CSS For The Login Container - (Holds The Username/Password Fields & Submit Button)
$this->LoginCss = '<style>

/* User Register Link */
.user_login_register {

}

#loginform {
width:200px;
}

/* Login Form */
#loginform label{
font-size:14px;font-weight:bold;
}

/* Login Username Field */
.user_login_username {
height:17px;
padding:2px;
border:1px solid black;
width:75px;
float:right;
}

/* Login Password Field */
.user_login_password {
height:17px;
padding:2px;
border:1px solid black;
width:75px;
float:right;
}

/* Login Submit Button */
.user_login_submit {
float:left;
padding:3px;
}

</style>																																																																																					';

//Welcome Box CSS -  Holds Welcome Message For User
$this->WelcomeCss = '<style>
/* Span Tag Around "Welcome" Text */
.user-login-welcome-label {
font-size:17px;font-weight:bold;
}

/* Logout Link */
.user-login-logout-link {

}

.user_login_welcome_box_container {
font-size:16px;width:100%;
}

</style>';

//Register Box CSS - CSS For All Fields/titles/labels For Register Box
$this->RegisterCss = '<style>
.user-login-register-title {
font-size:16px;font-weight:bold;
}

/* Register Form Labels */
#registerform label{
font-size:14px;font-weight:bold;
}
#registerform input{
font-size:14px;font-weight:bold;width:100px;float:right;
}
#registerform input[type=submit]{
float:left;
}

</style>';

			//Protected Message - The Formatting/text For Protected Message - Can Be Changed Via CKEDITOR Through Plugin Admin Page
			$this->ProtectedMessage = '<div style="width:100%;height:25px;clear:both;padding:20px;font-size:18px;">
			This Page Requires You To Be Logged In To View</div>';	
			
			//Create XML File
			$xml = new SimpleXMLElement('<item></item>');
			$xml->addChild('storage', $this->Storage);
			$xml->addChild('db_host', $this->DB_Host);
			$xml->addChild('db_user', $this->DB_User);
			$xml->addChild('db_pass', $this->DB_Pass);
			$xml->addChild('db_name', $this->DB_Name);
			$xml->addChild('db_table_name', $this->DB_Table_Name);
			$xml->addChild('storage', $this->Storage);
			$xml->addChild('errors', $this->Errors);
			$xml->addChild('email', $this->Email);
			$xml->addChild('logincontainer', $this->LoginCss);
			$xml->addChild('welcomebox', $this->WelcomeCss);
			$xml->addChild('protectedmessage', $this->ProtectedMessage);
			$xml->addChild('registerbox', $this->RegisterCss);
			$xml->addChild('first', $this->RegisterCss);
			if (! XMLsave($xml, FeulFile))
			{
				$error = i18n_r('CHMOD_ERROR');
			}
			else
			{
				echo '<div class="updated">Front-End User Login Settings Succesfully Created</div>';
			}
		}
		
		$feul_data = getXML(FeulFile);
		$this->Storage = $feul_data->storage;
		$this->Email= $feul_data->email;
		$this->LoginCss = $feul_data->logincontainer;
		$this->WelcomeCss =  $feul_data->welcomebox;
		$this->ProtectedMessage =  $feul_data->protectedmessage;
		$this->RegisterCss =  $feul_data->registerbox;
		$this->DB_Host = $feul_data->db_host;
		$this->DB_User = $feul_data->db_user;
		$this->DB_Pass = $feul_data->db_pass;
		$this->DB_Name = $feul_data->db_name;
		$this->DB_Table_Name = $feul_data->db_table_name;
		$this->Errors = $feul_data->errors;
		
		if(!empty($feul_data->first))
		{
			$this->first = 'Yes';
		}
		
		if($this->Storage == 'DB')
		{
			$this->dbh = $this->connectDB();
		}

		//Create data/site-users directory
		if(!file_exists(SITEUSERSPATH))
		{
			$create_feul_path = mkdir(SITEUSERSPATH);
			if($create_feul_path)
			{
				echo '<div class="updated">data/site-users Directory Succesfully Created</div>';
			}
			else
			{
				echo '<div class="error"><strong>The data/site-users folder could not be created!</strong><br/>You are going to have to create this directory yourelf for the plugin to work properly</div>';
			}
		}
	}
		
	/** 
	* Connects to database
	* 
	* @param string $hostname the hostname for the database 
	* @param string $username database username 
	* @param string $password database password 
	* @param string $db_name database name 
	* @return object the database connection
	*/  
	public function connectDB()
	{
		try 
		{
			$this->dbh = new PDO("mysql:host=".$this->DB_Host.";dbname=".$this->DB_Name, $this->DB_User, $this->DB_Pass);
			$this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); 
			/*** return database handle ***/
			return $this->dbh;
		}
		catch(PDOException $e)
		{
			if($this->Errors == 'On')
			{
				echo '<div class="error">'.$e->getMessage().'</div>';
			}
		}
}
		
	/** 
	* Check If Database Exists
	* 
	* @param string $hostname the hostname for the database 
	* @param string $username database username 
	* @param string $password database password 
	* @param string $db_name database name 
	* @return object the database connection
	*/  
	public function checkTable()
	{
		try
		{
			$create_table = $this->connectDB();
			$sth = $create_table->prepare("SELECT * FROM ".$this->DB_Table_Name);
			return $sth->execute();
		}
		catch(PDOException $e)
		{
		}

	}

	/** 
	* Create Database
	* 
	* @return bool
	*/  
	public function createDB()
	{
		try 
		{
			$this->dbh_create = new PDO("mysql:host=".$this->DB_Host, $this->DB_User, $this->DB_Pass);
			$this->dbh_create->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  
			/*** return database handle ***/

	        return $this->dbh_create->exec("CREATE DATABASE `$this->DB_Name`;
	                GRANT ALL ON `$this->DB_Name,`.* TO '$this->DB_User'@'".$this->DB_Host."';
	                FLUSH PRIVILEGES;");
		}
		catch(PDOException $e)
		{
			if($this->Errors == 'On')
			{	
				echo '<div class="error"><strong>Could Not Create Database:</strong> '.$e->getMessage().'</div>';
			}
		}
	}
	
	/** 
	* Create Database Table
	* 
	* @return bool
	*/  
	public function createDBTable()
	{
		try 
		{
			$query = "CREATE TABLE `".$this->DB_Table_Name."` (
					userID INT NOT NULL AUTO_INCREMENT, 
					PRIMARY KEY(userID),
					`Username` text,
					`Password` text,
					`EmailAddress` text
					)";
	        $this->dbh->exec($query);
		}
		catch(PDOException $e)
		{
			if($this->Errors == 'On')
			{
				echo '<div class="error"><strong>Could Not Create Table:</strong> '.$e->getMessage().'</div>';
			}
		}
	}

	/** 
    * Sets Gets data from xml file
    * 
	* @param string $field the xml node which will be returned
    * @return string the result of node request
    */  
	public function getData($field)
	{
		$feul_data = getXML(FeulFile);
		
		if(isset($feul_data->$field))
		{
			return $feul_data->$field;
		}
		else
		{
			return 'Error: Xml Field Does Not Exist';
		}
	}
				
	/** 
    * Gets all users 
    * 
    * @return array all saved searches matching userID
    */  
	public function getAllUsers()
	{
		$result = '';
		if($this->Storage == 'XML')
		{
			$dir = SITEUSERSPATH."*.xml";
			$count = 0;
			foreach (glob($dir) as $file) 
			{
				$count++;
				$result[$count] = simplexml_load_file($file) or die("Unable to load XML file!");
			}
		}
		elseif($this->Storage == 'DB')
		{
			try
			{	
				$sth = $this->dbh->prepare("SELECT * FROM ".$this->DB_Table_Name);
				$sth->execute();
				$result = $sth->fetchAll();
			}
			catch(PDOException $e)
			{
				if($this->Errors == 'On')
				{
					echo '<div class="error">'.$e->getMessage().'</div>';
				}
			}
		}
		else
		{
			return ( false );
		}
		// if the array count is 0
		if ( count ( $result ) == 0 )
		{
			return ( false );
		}
		else
		{
			return $result;
		}
	}
			
	/** 
    * Gets data from database for requested user
    * 
	* @param string $user the user to select in database
	* @param string $column the database column to get data from
    * @return string the result of user data request
    */  
	public function getUserData($user,$column,$table=null)
	{
		if($this->Storage == 'XML')
		{
			$user_xml = getXML(SITEUSERSPATH.$user.".xml");
			$user_data = $user_xml->$column;
			return $user_data;
		}
		
		elseif($this->Storage == 'DB')
		{
			try
			{
				$sql = "SELECT * FROM ".$this->DB_Table_Name." WHERE Username = '".$user."'";
				$stmt = $this->dbh->query($sql);
				$obj = $stmt->fetch(PDO::FETCH_OBJ);
			}
			
			catch(PDOException $e)
			{
				if($this->Errors == 'On')
				{
					echo '<div class="error">'.$e->getMessage().'</div>';
				}
			}
			
			if(is_object($obj))
			{
				return $obj->$column;
			}
			else
			{
				return '';
			}
		}
	}
	
	/** 
    * Gets data from database for requested user
    * 
	* @param string $user the user to select in database
	* @param string $column the database column to get data from
    * @return string the result of user data request
    */  
	public function getUserDataID($user,$column,$table=null)
	{
		$user = strtolower($user);
		if($this->Storage == 'XML')
		{
			$user_xml = getXML(SITEUSERSPATH.$user.".xml");
			$user_data = $user_xml->$column;
			return $user_data;
		}
		
		elseif($this->Storage == 'DB')
		{
			try
			{
				$sql = "SELECT * FROM ".$this->DB_Table_Name." WHERE userID = '".$user."'";
				$stmt = $this->dbh->query($sql);
				$obj = $stmt->fetch(PDO::FETCH_OBJ);
			}
			
			catch(PDOException $e)
			{
				if($this->Errors == 'On')
				{
					echo '<div class="error">'.$e->getMessage().'</div>';
				}
			}
			if(is_object($obj))
			{
				return $obj->$column;
			}
			else
			{
				return '';
			}
		}
	}

		
	/** 
    * Process settings form. Saves to xml file
    * 
    * @return void
    */  
	public function processSettings()
	{
		$this->Storage = $_POST['storage'];
		$this->LoginCss = safe_slash_html($_POST['post-login-container']);
		$this->Email = $_POST['post-from-email'];
		$this->WelcomeCss = safe_slash_html($_POST['post-welcome-box']);
		$this->ProtectedMessage = safe_slash_html($_POST['post-protected-message']);
		$this->RegisterCss = safe_slash_html($_POST['post-register-box']);
		$this->DB_Host = $_POST['db_host'];
		$this->DB_User = $_POST['db_user'];
		$this->DB_Pass = $_POST['db_pass'];
		$this->DB_Name = $_POST['db_name'];
		$this->DB_Table_Name = $_POST['db_table_name'];
		$this->Errors = $_POST['errors'];

		# create xml file
		if (file_exists(FeulFile)) 
		{ 
			unlink(FeulFile); 
		}	
		$xml = new SimpleXMLElement('<item></item>');
		$xml->addChild('storage', $this->Storage);
		$xml->addChild('email', $this->Email);
		$xml->addChild('db_host', $this->DB_Host);
		$xml->addChild('db_user', $this->DB_User);
		$xml->addChild('db_pass', $this->DB_Pass);
		$xml->addChild('db_name', $this->DB_Name);
		$xml->addChild('db_table_name', $this->DB_Table_Name);
		$xml->addChild('storage', $this->Storage);
		$xml->addChild('errors', $this->Errors);
		$xml->addChild('logincontainer', $this->LoginCss);
		$xml->addChild('welcomebox', $this->WelcomeCss);
		$xml->addChild('protectedmessage', $this->ProtectedMessage);
		$xml->addChild('registerbox', $this->RegisterCss);
		if (! XMLsave($xml, FeulFile))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/** 
    * Adds new user in database
    * 
	* @param string $username, the new account username
	* @param string $password the new account password
	* @param string $column the new account email
    * @return bool
    */  
	public function processAddUserAdmin($username, $password, $email, $convert=null)
	{
		if($convert == null)
		{
			$password = md5($password);
		}

		$username = strtolower($username);
		
		if($this->Storage == 'XML')
		{
			$usrfile = strtolower($username);
			$usrfile	= $usrfile . '.xml';

			// create user xml file - This coding was mostly taken from the 'settings.php' page..
			$xml = new SimpleXMLElement('<item></item>');
			$xml->addChild('Username', $username);
			$xml->addChild('Password', $password);
			$xml->addChild('EmailAddress', $email);
			if (! XMLsave($xml, SITEUSERSPATH . $usrfile) ) 
			{
				return false;
			}
			else
			{
				return true;
			}
		}

		elseif($this->Storage == 'DB')
		{
			//Check if username already exists
			$check_sql = "SELECT Username FROM ".$this->DB_Table_Name." WHERE Username = '".$username."'";
			try
			{
				$sth = $this->dbh->prepare($check_sql);
				$sth->execute();
			}
			catch(PDOException $e)
			{
				if($this->Errors == 'On')
				{
					echo '<div class="error">Error: '.$e->getMessage().'</div>';
				}
			}
			
			//If Username Is taken Notify User To Choose A Differant One
			if($sth->fetchColumn() != '')
			{
				return false;
			}

			//If username is not taken - Create Account And Log User In
			else
			{
				try
				{
					$sql = "INSERT INTO ".$this->DB_Table_Name." (Username, Password, EmailAddress) VALUES (:Username,:password,:email)";
					$q = $this->dbh->prepare($sql);
					$add_array = array(':Username'=>$username,
									  ':password'=>$password,
									  ':email'=>$email);
					$attemt_addition = $q->execute($add_array);
				}
				catch(PDOException $e)
				{
					if($this->Errors == 'On')
					{
						echo '<div class="error">'.$e->getMessage().'</div>';
					}
				}
			}
			if($attemt_addition)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	/** 
    * Process Edit User Form XML
    * 
    * @return bool
    */  
	public function processEditUser($name=null, $password=null, $email=null, $change_pass=null, $change_name=null)
	{
		$usrfile = strtolower($name);
		$usrfile	= $usrfile . '.xml';
		
		if($change_name != null)
		{
			unlink(SITEUSERSPATH . $usrfile);
			$usrfile = strtolower($change_name);
			$usrfile	= $usrfile . '.xml';
			$name = $change_name;
		}
		
		if($change_pass != null)
		{
			$password = md5($change_pass);
		}

		// create user xml file - This coding was mostly taken from the 'settings.php' page..
			$xml = new SimpleXMLElement('<item></item>');
			$xml->addChild('Username', $name);
			$xml->addChild('Password', $password);
			$xml->addChild('EmailAddress', $email);
			if (! XMLsave($xml, SITEUSERSPATH . $usrfile) ) {
				$error = i18n_r('CHMOD_ERROR');
			}
			
			else
			{
				print '<div class="updated">User sucesfully edited</div>';
			}
	}
	
	/** 
    * Process Edit User Form DATABASE
    * 
    * @return bool
    */  
	public function processEditDBUser($id, $name=null, $password=null, $email=null)
	{
		if(!empty($_SESSION['userID']))
		{
			$id = $_SESSION['userID'];
		}
		
		$sql = "UPDATE ".$this->DB_Table_Name." SET Username=?, Password=?, EmailAddress=? WHERE userID = '".$id."'";
		$edit_array = array($name,$password,$email);
		$q = $this->dbh->prepare($sql);
		$q->execute($edit_array);
		//print '<div class="updated">User sucesfully edited</div>';
	}
		
	/** 
    * Deletes a user from database
    * 
	* @param string $username the user to delete
    * @return bool
    */  
	public function deleteUser($user)
	{
		if($this->Storage == 'XML')
		{
			$usrfile = strtolower($user);
			$usrfile	= $usrfile . '.xml';
			$delete_file = unlink(SITEUSERSPATH . $usrfile);
			if($delete_file)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		elseif($this->Storage == 'DB')
		{
			try
			{			
				$sql = "DELETE FROM ".$this->DB_Table_Name." WHERE userID = '".$user."'";
				$q = $this->dbh->prepare($sql);
				$success = $q->execute();
			}
			catch(PDOException $e)
			{
				if($this->Errors == 'On')
				{
					echo '<div class="error">'.$e->getMessage().'</div>';
				}
			}
			if ($this->getUserDataID($user, 'userID') == '')
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/** 
    * Email user(s)
    * 
	* @param string $to email address(es) to send to
	* @param string $to_name name of recipitent
	* @param string $from the email address from which to send from
	* @param string $subject the subject of the email
	* @param string $message_content the body of the message
    * @return bool
    */  
	public function processEmailUser($to, $to_name, $from, $subject=null, $message_content)
	{
		$success = '';
		// subject
		if($subject == null)
		{
			$subject = $_POST['subject'];
		}

		// message
		$message = '
		<html>
		<head>
		  <title>'.$subject.'</title>
		</head>
		<body>
			'.$message_content.'
		</body>
		</html>
		';

		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// Additional headers
		//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
		$headers .= 'From: '.$from . "\r\n";
		//$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
		//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

		// Mail it
		$success = mail($to, $subject, $message, $headers);
		if($success)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	
	/** 
    * Check to see if a user is logged in and set session var -
    * Also logs user out if they click the logout link 
	*
    * @return void
    */  
	public function checkLogin($auto_login=null, $auto_username=null, $auto_password=null)
	{
		global $SITEURL;
		global $logged_in_message;
		//Check If User Session Is Started - If It IS NOT* Started, Then Start Session
		if(!isset($_SESSION))
		{
			session_start();
		}
		
		//I DONT THINK THE BELOW VARIABLE IS NEEDED
		//$the_page_slug =  return_page_slug();

		//Check If Logged In - Define Username - If logged in return true
		if(!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username']))
		{
			//If Logged In The $logged_in Variable = true
			return true;
		}

		//If Not Logged In But USername And Password Have Been Posted
		elseif(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['login-form']))
		{
			//Set Posted Username And Password Variable
			$username = strtolower($_POST['username']);
			$password = $_POST['password'];

			if($this->Storage == 'XML')
			{
				$user_pass = $this->getUserData($username,'Password');
			}
			elseif($this->Storage == 'DB')
			{
				$user_pass = $this->getUserData($username,'Password');
			}

			//Check XML File Password.. If Correct, Log User In And Redirect Back To Page
			if(md5($password) == $user_pass)
			{
				$_SESSION['Username'] = $_POST['username'];
				$_SESSION['LoggedIn'] = 1;
				if($this->Storage == 'DB')
				{
					$_SESSION['userID'] = $this->getUserData($username,'userID');
				}
				return true;
			}
			//If Login Credentials were wrong.. Do Not Log Visitor In And Return wrong information message
			else
			{
				return false;
			}
		}
		elseif($auto_login != null)	
		{
			$_SESSION['Username'] = $auto_username;
			$_SESSION['LoggedIn'] = 1;
			if($this->Storage == 'DB')
			{
				$_SESSION['userID'] = $this->getUserData($auto_username,'userID');
			}
		}
	}
	
	/** 
    * Check to see if a front end page is for members only
    * Display protected message if page is members only and user is not logged in
	*
    * @return bool
    */  
	public function checkPerm()
	{
		//Get Current Page Slug And XML File
		$the_page_slug_xml = GSDATAPAGESPATH.return_page_slug().'.xml';
		$page_data = getXML($the_page_slug_xml);

		//Check If Page Is For Members Only
		if($page_data->memberonly == "yes")
		{
			//If The Page Is For Members Only And The User Is Not Logged In Display protected Message
			if(!isset($_SESSION['LoggedIn']))
			{
				return false;
			}

			//If Page Is For Members Only And The User Is* Logged In - Display Normal Content
			else
			{
				return true;
			}
		}	

		//If The Page Is NOT For Members Only - Display Normal Content
		else
		{
			return true;
		}
	}
	
	/** 
    * Check if an existing page has the 'Members Only' checkbox checked
	*
    * @return bool
    */
	public function showMembersPermBox()
	{
		//Check If Checkbox For Current Page Editing Is Already Checked
		if(isset($_GET['id']))
		{
			$current_page_edit = $_GET['id'];
			$the_page_slug_xml = GSDATAPAGESPATH.$current_page_edit.'.xml';
			$page_data = getXML($the_page_slug_xml);

			//Check If Page Is For Members Only
			if($page_data->memberonly == 'yes')
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	public function convertXmlToDB()
	{
		$dir = SITEUSERSPATH."*.xml";
		// Make Edit Form For Each User XML File Found
		foreach (glob($dir) as $file) 
		{
			$xml = simplexml_load_file($file) or die("Unable to load XML file!");
			$addUser = $this->processAddUserAdmin($xml->Username, $xml->Password, $xml->EmailAddress, true);
		}
	}
}

?>