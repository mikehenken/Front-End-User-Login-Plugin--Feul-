<?php
/*
Plugin Name: User Login XML
Description: Allow users to create an account and login to your website. Protects pages. XML Based.
Version: 3.1
Author: Mike Henken
Author URI: http://michaelhenken.com/
*/

# get correct id for plugin
$thisfile=basename(__FILE__, ".php");

# register plugin
register_plugin(
	$thisfile, 
	'Front-End User Login', 
	'3.1', 			
	'Mike Henken',	
	'http://michaelhenken.com/', 
	'Allow users to create an account and login to your website. Protects pages. Uses XML Or Database.', 
	'settings', 
	'user_login_admin' 
);

# hooks

//Adds 'Members Only' Check Box In edit.php
add_action('edit-extras','user_login_edit');

//Saves 'Members Only' checkbox selection when a page is saved
add_action('changedata-save','user_login_save');

//Filter Out The Content (Checks To See If Page Is Protected Or Not)
add_filter('content','perm_check');

//Launch Function 'user_login_check' before the template is loaded on the front-end
add_action('index-pretemplate','user_login_check');

//Add tab in admin navigation bar
add_action('nav-tab','makeNavTab');

//Define Feul Settings File
define('FeulFile', GSDATAOTHERPATH  . 'user-login.xml');

//Define User Login Plugin's plugins Folder (plugins/user-login/)
define('USERLOGINPATH', GSPLUGINPATH . 'user-login/');

//Define User Login Plugin's plugins includes Folder (plugins/user-login/inc/)
define('LOGININCPATH', GSPLUGINPATH . 'user-login/inc/');

//Define The User Data Storage Folder (data/site-users)
define('SITEUSERSPATH', GSDATAPATH . 'site-users/');



function makeNavTab()
{
	$plugin = 'user_login';
	$class = '';
	$txt = '<em>U</em>ser Management';
	if (@$_GET['id'] == @$plugin) {
		$class='class="tabSelected"';
	}
	echo '<li><a href="load.php?id='.$plugin.'" '.$class.' >';
	echo $txt;
	echo "</a></li>";
}	

//Include Feul class
require_once(USERLOGINPATH.'class/Feul.php');

/** 
* Check If Page Is Protected Or Not. 
* Then, Either Blocks Access Or Shows Content, Depending On If Logged In Or Not
*
* @param string $content the content for the page.. It comes through the content filter (hook)
*
* @return string content or page protected message
*/  
function perm_check($content)
{
	$Feul = new Feul;
	if($Feul->checkPerm() == true)
	{
		return $content;
	}
	else
	{
		return $Feul->getData('protectedmessage');
	}
}

/** 
* Proccess Admin User Login Settings and various contional statements related to this plugin.
*
* @return string content or page protected message
*/  
function user_login_admin()
{
	$Feul = new Feul;
	if(isset($Feul->first) && !isset($_GET['settings']))
	{
		echo '<div class="error"><a href="load.php?id=user_login&settings">Click Here</a> to Select Storage Method &amp; other settings.</div>';
	}
?>	
	<link rel="stylesheet" type="text/css" href="../plugins/user-login/css/admin_style.css" />
	<div style="width:100%;margin:0 -15px -15px -10px;padding:0px;">
		<h3  class="floated">Front-End User Login Plugin</h3>
		<div class="edit-nav clearfix">
			<p>
				<a href="load.php?id=user_login&help">Help</a>
				<a href="load.php?id=user_login&settings">Settings</a>
				<a href="load.php?id=user_login&email=yes">Email Users</a>
				<a href="load.php?id=user_login&manage=yes">Manage Users</a>
			</p>
		</div>
	</div>
	</div>
	<div class="main" style="margin-top:-10px;">
	<?php
	if(isset($_GET['email']))
	{
		if(isset($_POST['send-email']))
		{	
			if(isset($_POST['send_all']))
			{
				if($Feul->Storage == 'XML')
				{
					$dir = SITEUSERSPATH."*.xml";
					// Make Edit Form For Each User XML File Found
					foreach (glob($dir) as $file) 
					{
						$xml = simplexml_load_file($file) or die("Unable to load XML file!");
						$Feul->processEmailUser($xml->EmailAddress, $xml->Username, $_POST['email'], $_POST['subject'], $_POST['post-email-message']);
					}
					echo '<div class="updated">Emails Successfully Sent</div>';
				}

				elseif($Feul->Storage == 'DB')
				{
					try 
					{
						$sql = "SELECT * FROM ".$this->DB_Table_Name;
						foreach ($this->dbh->query($sql) as $row)
						{
							$Feul->processEmailUser($row['EmailAddress'], $row['Username'], $_POST['email'], $_POST['subject'], $_POST['post-email-message']);
						}
						echo '<div class="updated">Emails Successfully Sent</div>';
					}
					catch(PDOException $e)
					{
						echo '<div class="error">Error: '.$e->getMessage().'</div>';
					}
				}
			}
			else
			{
				$emails = $Feul->processEmailUser($_POST['email_to'], null, $_POST['email'], $_POST['subject'], $_POST['post-email-message']);
				if($emails != false)
				{
					echo '<div class="updated">Email Successfully Sent</div>';
				}
			}
		}
	global $text_area_name;
	$text_area_name = 'post-email-message';
	?>
		<h3>Send Email To Users</h3>
		<form method="post" action="load.php?id=user_login&email=yes&send-email=yes">
			<p>
				<label for="from-email">"From" Email Address</label>
				<input type="text" name="email" class="text" value="<?php echo $Feul->getData('email'); ?>" />
			</p>
			<div style="padding:10px;margin-bottom:15px;background-color:#f6f6f6;">
			<p>
				<label style="font-size:15px;padding-bottom:3px;">Check This Box To Send To All Users</label>
				<input type="checkbox" name="send_all" />
			</p>
			<p style="margin-top:-10px; margin-bottom:10px;">
				<label style="font-size:18px;color:red;">OR:</label>
			</p>
			<p>
				<label for="subject" style="font-size:15px;padding-bottom:3px;">Enter Recipitent's email address(s) here. Seperated by comma</label>
				<input type="text" name="email_to" class="text" value="" />
			</p>
			</div>
			<p>
				<label for="subject">Email Subject</label>
				<input type="text" name="subject" class="text" value="" />
			</p>
			<label for="email-message">Message:</label>
			<textarea name="post-email-message"></textarea>
			<?php include(USERLOGINPATH . 'ckeditor.php'); ?>
			<input type="submit" class="submit" name="send-email" value="Submit" />
		</form>
	<?php
	}
	elseif(isset($_GET['settings']))
	{
		if(isset($_POST['storage']))
		{
			$submit_settings = $Feul->processSettings();
			if($submit_settings == true)
			{
				echo '<div class="updated">Front-End User Login Settings Succesfully Edited</div>';
			}
			else
			{
				echo '<div class="error">Settings Could Not Be Saved!</div>';
			}
		}
		elseif(isset($_GET['create_db']))
		{	
			$create_db = $Feul->createDB();
			if($create_db != false)
			{
				echo '<div class="updated">Database Created</div>';
			}
		}
		elseif(isset($_GET['create_tb']))
		{
			$check_table = $Feul->checkTable();
			if($check_table == '1')
			{
				echo '<div class="error">Database Table Already Exists</div>';
			}
			else
			{
				$create_table = $Feul->createDBTable();
				$check_table_again = $Feul->checkTable();
				if($check_table_again == '1')
				{
					echo '<div class="updated">Database Table Created</div>';
				}
				elseif($check_table_again != 1)
				{
					echo '<div class="error">Database Table Could Not Be Created</div>';
				}
			}
		}
		?>			
			<form method="post">
			<h2>Storage Settings</h2>
			<p>
				<label>Choose Storage Method</label>
				<input type="radio" name="storage" value="XML" <?php if($Feul->Storage == 'XML') { echo ' CHECKED'; } ?> /> XML
				<br/>
				<input type="radio" name="storage" value="DB" <?php if($Feul->Storage == 'DB') { echo ' CHECKED'; } ?> /> Database
			</p>
			
			<h4>Database Settings</h4>
			<p>
				<label>Database Host</label>
				<input type="text" class="text full" name="db_host" value="<?php if($Feul->DB_Host == '') { } else { echo $Feul->DB_Host; } ?>" />
			</p>
			<p>
				<label>Database User</label>
				<input type="text" class="text full" name="db_user" value="<?php if($Feul->DB_User == '') { } else { echo $Feul->DB_User; } ?>" />
			</p>
			<p>
				<label>Database Password</label>
				<input type="text" class="text full" name="db_pass" value="<?php if($Feul->DB_Pass == '') {  } else { echo $Feul->DB_Pass; } ?>" />
			</p>
			<p>
				<label>Database Name</label>
				You can choose any database name you would like. <strong>If the auto-creation fails</strong> you might need to prefix the db_name with your mysql username (ex: username_dbname)
				<input type="text" class="text full" name="db_name" value="<?php if($Feul->DB_Name == '') { echo ''; } else { echo $Feul->DB_Name; } ?>" />
			</p>
			<p>
				<label>Database Table Name</label>
				We strongly recommend leaving this as 'users'<br/>
				<input type="text" class="text full" name="db_table_name" value="<?php if($Feul->DB_Table_Name == '') { echo 'users'; } else { echo $Feul->DB_Table_Name; } ?>" />
			</p>
			<p>
				<label>PDO Errors (Database Error Messages)</label>
				<input type="radio" name="errors" value="On" <?php if($Feul->Errors == 'On') { echo ' CHECKED'; } ?> /> Enable
				<br/>
				<input type="radio" name="errors" value="Off" <?php if($Feul->Errors == 'Off') { echo ' CHECKED'; } ?> /> Disable
			</p>
			<p>
				<input type="submit" name="Feul_settings_form" class="submit" value="Submit" />
			</p>
			<p>
				<a href="load.php?id=user_login&settings&create_db">Attempt Create Database</a><br/>
				<a href="load.php?id=user_login&settings&create_tb">Attempt Create DB Table</a>
			</p>
			</div>
			<div class="main" style="margin-top:-10px;">
				<h2>Email Settings</h2>
				<p>
					<label>Edit Registration "From" Email Address</label>This is the "From" address that shows in the "Registration Email" the user gets upon registering:<br/>
					<input type="text" name="post-from-email" class="text full" value="<?php echo $Feul->getData('email'); ?>" />
				</p>
				
			</div>
			<div class="main" style="margin-top:-10px;">
			<h2>CSS &amp; Protected Message</h2>
				<p>
					<label>Edit Login Container CSS</label>
					<textarea name="post-login-container" class="full" style="height:300px;">
						<?php echo $Feul->LoginCss; ?>
					</textarea>
				</p>
				<p>
					<label>Edit Welcome Box CSS</label>
					<textarea name="post-welcome-box" class="full" style="height:300px;">
						<?php echo $Feul->WelcomeCss; ?>
					</textarea>
				</p>
				<p>
					<label>Edit Register Box CSS</label>
					<textarea name="post-register-box" class="full" style="height:300px;">
						<?php echo $Feul->RegisterCss; ?>
					</textarea>
				</p>
				<p>
					<label>Edit Protected Message</label>
					<textarea name="post-protected-message">
						<?php global $text_area_name; $text_area_name = 'post-protected-message'; echo $Feul->ProtectedMessage; ?>
					</textarea>
					</p>
				<?php include(USERLOGINPATH . 'ckeditor.php'); ?>
				<p>
					<input type="submit" name="Feul_settings_form" class="submit" value="Submit" />
				</p>
			</form>
			<br/>
			<?php
	}
	elseif(isset($_GET['edit_user']))
	{
		if(isset($_POST['Feul_edit_user']))
		{
			if($_POST['old_name'] != $_POST['usernamec'])
			{
				$change_name = $_POST['usernamec'];
			}
			else
			{
				$change_name = null;
			}

			$posted_password = $_POST['nano'];	
			if(isset($_POST['userpassword']))
			{
				$change_pass = $_POST['userpassword'];
			}
			else
			{
				$change_pass = null;
			}
			
			
			if($Feul->Storage == 'XML')
			{
				$Feul->processEditUser($_POST['old_name'], $posted_password, $_POST['useremail'], $change_pass, $change_name);
			}
			elseif($Feul->Storage == 'DB')
			{
				$Feul->processEditDBUser($_POST['userID'], $_POST['usernamec'], $posted_password, $_POST['useremail']);
			}
			if($change_name != null)
			{
				print '<meta http-equiv="refresh" content="0;url=load.php?id=user_login&edit_user='.$change_name.'">';
			}
		}
		editUser($_GET['edit_user']);
	}
	elseif(isset($_GET['help']))
	{
		if(isset($_GET['convert']))
		{
			$convert = $Feul->convertXmlToDB();
			echo '<div class="updated">Users Sucesfully Converted</div>';
		}
	?>
		<h2>Plugin Information:</h2>

		<h4>Functions</h4>

		<p>
			<label>Display Login Form:</label>
			<?php highlight_string('<?php echo show_login_box(); ?>'); ?>
		</p>

		<p>
			<label>Display Welcome Message:</label>
			This is displayed if the user is logged in. It consists of "Welcome Username" and a logout link.<br/>
			<?php highlight_string('<?php echo welcome_message_login(); ?>'); ?>
		</p>

		<p>
			<label>Display Register Form:</label>
			<?php highlight_string('<?php user_login_register(); ?>'); ?>
		</p>

		<h4>Showing Content To Only Logged In Users</h4>
		<ol>
			<li>You could block access to a particular page by choosing "Members Only" in the "Page Options" for that page.<br/>
				If a page is "Members Only", when a user is not logged in they will see the "Protected Message" which can be changed <a href="load.php?id=user_login&settings">here</a>
			</li><br/>
			<li>
				If you would like "Members Only" content in your template you will have to use a little php. <br/> Below is an example of how to display "Hello World" to only logged in users.<br/>
<pre>
<?php highlight_string('<?php if(!empty($_SESSION[\'LoggedIn\']))	{ ?>'); ?>
	Helo World
<?php highlight_string('<?php } ?>'); ?>
</pre>
			</li>
		</ol>

		<h4>Further Help &amp; Support</h4>
		<p>
			If you run into any bugs/errors or need any assitance please visit the support forums <a href="http://get-simple.info/forum/topic/2342/front-end-user-login-plugin-xml-ver-25/">Here</a>
		</p>
		</div>

		<div class="main" style="margin-top:-10px;">
		<h2>Converting XML Users To DB Users</h2>
		<p>
			Clicking the below link will convert all xml users to your database.<br/>
			<strong>Your database information on the settings page needs to be filled out and the database and table need to be created before converting.</strong><br/>
			<a href="load.php?id=user_login&help&convert">Convert Users</a>
		</p>
	<?php
	}
	else
	{
		if(isset($_GET['manage']))
		{
			if(isset($_GET['adduser']))
			{		
				$Add_User = $Feul->processAddUserAdmin($_POST['usernamec'],$_POST['userpassword'],$_POST['useremail']);
				if($Add_User == false) 
				{
					echo '<div class="error">Username Already Taken</div>';
				}
				else
				{
					echo '<div class="updated">User Successfully Added</div>';
				}
			}
			elseif (isset($_GET['deleteuser'])) 
			{
				if($Feul->Storage == 'XML')
				{
					$deleteUser = $Feul->deleteUser($_GET['deletename']);
				}			
				elseif($Feul->Storage == 'DB')
				{
					$deleteUser = $Feul->deleteUser($_GET['deleteuser']);
				}
				if($deleteUser == true)
				{
					echo '<div class="updated" style="display: block;">'.$_GET['deletename'].' Has Been Successfully Deleted</div>';
				}
				else
				{
					echo '<div class="updated" style="display: block;"><span style="color:red;font-weight:bold;">ERROR!!</span> - Unable To Delete User</div>';
				}
			}
		}
		manageUsers();
	}
}

function manageUsers()
{
	$Feul = new Feul;
	$users = $Feul->getAllUsers();
	if($Feul->Storage == 'DB')
	{
		$users = (array) $users;
	}
		?>
		<div id="profile" class="hide-div section" style="display:none;margin-top:-30px;">
			<form method="post" action="load.php?id=user_login&manage=yes&adduser=yes">
				<h3>Add New User</h3>
				<div class="leftsec">
					<p>
						<label for="usernamec" >Username:</label>
						<input class="text" id="usernamec" name="usernamec" type="text" value="" />
					</p>
				</div>
				<div class="rightsec">
					<p>
						<label for="useremail" >Email :</label>
						<input class="text" id="useremail" name="useremail" type="text" value="" />
					</p>
				</div>
				<div class="leftsec">
					<p>
						<label for="userpassword" >Password:</label>
						<input autocomplete="off" class="text" id="userpassword" name="userpassword" type="text" value="" />
					</p>
				</div>
				<div class="clear"></div>
				<p id="submit_line" >
					<span>
						<input class="submit" type="submit" name="submitted" value="Add New User" />
					</span> &nbsp;&nbsp;<?php i18n('OR'); ?>&nbsp;&nbsp; 
					<a class="cancel" href="#"><?php i18n('CANCEL'); ?></a>
				</p>
			</form>
		</div>
		<h3 class="floated">User Management</h3>
		<div class="edit-nav clearfix">
			<p>
				<a href="#" id="add-user">Add New User</a>
			</p>
		</div>
		<?php
		if($users != false) 
		{ 
		?>
			<table class="highlight" style="width:900px">
				<tr>
					<th>Name</th>
					<th>Email</th>
				<tbody>
			<?php
			// Make Edit Form For Each User XML File Found
			foreach ($users as $row)
			{
				if($Feul->Storage == 'XML')
				{
					$Username = $row->Username;
					$EmailAddress = $row->EmailAddress;
				}
				elseif($Feul->Storage == 'DB')
				{
					$userID =  $row['userID'];
					$Username = $row['Username'];
					$EmailAddress = $row['EmailAddress'];
				}

				//Below is the User Data
				?>	
				<tr>
					<td>
						<a href="load.php?id=user_login&edit_user=<?php if($Feul->Storage == 'XML') { echo $Username; } else { echo $userID; } ?>"><?php echo $Username; ?></a>
					</td>
					<td>
						<?php echo $EmailAddress; ?>
					</td>
				</tr>
			<?php } ?>
				</tbody>
			</table>
		<?php 
		}
		elseif($users == false)
		{
			echo '<p><strong>No Users Exist</strong></p>';
		}
		?>
	<script type="text/javascript">
		
		/*** Show add-user form ***/
		$("#add-user").click(function () {
			$(".hide-div").show();
			$("#add-user").hide();
		});
		
		/*** Hide user form ***/
		$(".cancel").click(function () {
			$(".hide-div").hide();
			$("#add-user").show();
		});
	</script>
	<?php
}

function editUser($id)
{
	$id = urldecode($id);
	$Feul = new Feul;
	?>
	<h3>User Information</h3>
	<form method="post" action="load.php?id=user_login&edit_user=<?php echo $id; ?>">
		<div class="leftsec">
			<p>
				<label for="usernamec" >Name:</label>
				<input class="text" id="usernamec" name="usernamec" type="text" value="<?php echo $Feul->getUserDataID($id,'Username'); ?>" />
			</p>
		</div>
		<div class="rightsec">
			<p>
				<label for="useremail" >Email :</label>
				<input class="text" id="useremail" name="useremail" type="text" value="<?php echo $Feul->getUserDataID($id,'EmailAddress'); ?>" />
			</p>
		</div>
		<div class="leftsec">
			<p>
				<label for="userpassword" >Change Password:</label>
				<input autocomplete="off" class="text" id="userpassword" name="userpassword" type="text" value="" />
			</p>
		</div>
		<div class="clear"></div>
		<p id="submit_line">
			<span>
				<input class="submit" type="submit" name="Feul_edit_user" value="Submit Changes" /> &nbsp;&nbsp;Or&nbsp;&nbsp; <a class="cancel" style="color: #D94136;text-decoration:underline;cursor:pointer" ONCLICK="decision('Are You Sure You Want To Delete <?php echo $Feul->getUserDataID($id,'Username'); ?>?','load.php?id=user_login&manage=yes&deleteuser=<?php echo $id; ?>&deletename=<?php echo $Feul->getUserDataID($id,'Username'); ?>')">Delete User</a>
				<input type="hidden" name="nano" value="<?php echo $Feul->getUserDataID($id,'Password'); ?>"/>
				<input type="hidden" name="old_name" value="<?php echo $Feul->getUserDataID($id,'Username'); ?>"/>
				<input type="hidden" name="userID" value="<?php echo $id; ?>"/>
			</span>
		</p>
	</form>
	<script type="text/javascript">
		/*** Confirm the user wants to delete a user ***/
		function decision(message, url){
			if(confirm(message)) location.href = url;
		}
	</script>
	<?php
}


/*******
Function To: 
Displays Login Box On Front-End Of Website
*******/
function show_login_box()
{
	$Feul = new Feul;
	//If The User Is Not Logged In - Display Login Box - If They Are Logged In, Display Nothing
	if(!isset($_SESSION['LoggedIn']))
	{	
		echo $Feul->getData('logincontainer');
		$is_loggedIn = $Feul->checkLogin();
		//HTML Code For Login Container
		?>
		<div id="login_box" style="">
			<h2 class="login_h2">Login</h2>
			<?php
				if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['login-form']) && $is_loggedIn == false)
				{
					echo '<div class="error">Sorry, your account could not be found. Please try again.</div>';
				}
			?>
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" name="loginform" id="loginform">
				<p>
					<label for="username">Username: </label>
					<input type="text" name="username" class="user_login_username" />
				</p>
				<p>
					<label for="username">Password: </label>
					<input type="password" name="password" class="user_login_password" />
				</p>
				<p>
					<input type="submit" class="user_login_submit" value="Submit"/>
				</p>
				<input type="hidden" name="login-form" value="login" />
			</form>
			<div style="clear:both"></div>
		</div>
		<?php
	}
}

/*******
Function To: 
Show Welcome Message If User Is Logged In - If Not Logged In, Display nothing
*******/
function welcome_message_login()
{
	global $SITEURL;
	$Feul = new Feul;
	if(isset($_SESSION['LoggedIn']))
	{
		$name  = $_SESSION['Username'];
		//Display Welcome Message
		$welcome_box = '<div class="user_login_welcome_box_container"><span class=\"user-login-welcome-label\">Welcome: </span>'.$name.'</div>';

		//Display Logout Link
		$logout_link = '<a href="'.$SITEURL.'?logout=yes" class="user-login-logout-link">Logout</a>';
		echo $Feul->getData('welcomebox').$welcome_box.$logout_link ;
	}
}

/*******
Function To: 
Check If User Is Logged In - Also Starts Session And Connects To Database
*******/
function user_login_check()
{
	$Feul = new Feul;
	$Feul->checkLogin();
	/* 
	If Logout Link Is Clicked:
	Log Client Out (End Session) 
	*/
	if(isset($_GET['logout']))
	{
		if(!empty($_SESSION['LoggedIn']) && !empty($_SESSION['Username']))
		{
			$_SESSION = array(); 
			session_destroy();
		}
	}	
}


/*******
Function To: 
Register Form And Processing Code - Display's And Processes Register Form
*******/
function user_login_register()
{
	global $SITEURL;
	$Feul = new Feul;
	$error = '';
	//If User Is Not Logged In
	if(!isset($_SESSION['LoggedIn']))
	{
		if(isset($_POST['register-form']))
		{
			//If Register Form Was Submitted
			if($_POST['username'] != '' && $_POST['password'] != '' && $_POST['email'] != '')
			{		
				$addUser = $Feul->processAddUserAdmin($_POST['username'], $_POST['password'], $_POST['email']);
				if($addUser == true)
				{
					echo '<div class="success">Your account was successfully created</div>';
					$Feul->checkLogin(true, $_POST['email'], $_POST['password']);
					//Send Email
					$to  = $_POST['email'];
					$Username = $_POST['username'];
					$chosen_password = $_POST['password'];

					// subject
					$subject = 'Your New Account ('.$Username.') Is Setup!';

					// message
					$message = '
					<html>
					<head>
					<title>Your New Account Is Setup!</title>
					</head>
					<body>
					<h2><strong>Below is your login information:</strong></h2><br/><br/>
					<strong>Username: </strong>'.$Username.'<br/>
					<strong>Password: </strong>'.$chosen_password.'<br/>
					<br/>
					<a href="'.$SITEURL.'">Click Here To Visit Website</a>
					</body>
					</html>
					';

					// To send HTML mail, the Content-type header must be set
					$headers  = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

					// Additional headers
					//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
					$headers .= 'From: New Account <'.$Feul->getData('email').'>' . "\r\n";
					//$headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
					//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";

					// Mail it
					$success = mail($to, $subject, $message, $headers);
					if(!$success)
					{
						$error = '<div class="error">Unable to send welcome email.</div>';
					}
				}
				else
				{
					$error = '<div class="error">User Already Exists</div>';
				}
			}
			else
			{
				$error = '<div class="error">Please fill in the required fields</div>';
			}
		}
		echo $Feul->getData('registerbox');
		?>
			<?php echo $error; ?>
			<h2 class="register_h2">Register</h2>
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" name="registerform" id="registerform">
				<p>
					<label for="username" class="required" >Username:</label>
					<input type="text" class="required" name="username" id="name" />
				</p>
				<p>
					<label for="email" class="required" >Email Address:</label>
					<input type="text" class="required" name="email" />
				</p>
				<p>
					<label for="password" class="required" >Password:</label>
					<input type="password" class="required" name="password" id="password" />
				</p>
				<p>
					<input type="submit" name="register" id="register" value="Register" />
					<input type="hidden" name="register-form" value="yes" />
				</p>
			</form>
		<?php
	}	
}

//Displays members Only Checkbox In edit.php
function user_login_edit()
{
	$Feul = new Feul;
	$member_checkbox = '';
	if($Feul->showMembersPermBox() == true)
	{
		$member_checkbox = "checked";	
	}
	?>
		<div class="leftopt" style="margin-top:20px;">
			<p class="inline">
				<label for="member-only">Members Only:</label> 
				<input type="checkbox" value="yes" name="member-only" style="" <?php echo $member_checkbox; ?> />
			</p>
		</div> 
	<?php
}

//Saves Value Of Checkbox in function - user_login_edit()
function user_login_save()
{
	global $xml;
	if(isset($_POST['member-only']))
	{ 
		$node = $xml->addChild(strtolower('memberonly'))->addCData(stripslashes($_POST['member-only']));	
	}
}