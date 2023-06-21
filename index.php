<?php

#**********************************************************************************#


                #****************************************#
                #********** PAGE CONFIGURATION **********#
                #****************************************#

                require_once('./include/config.inc.php');
                require_once('./include/form.inc.php');
                require_once('./include/db.inc.php');
                require_once('./include/dateTime.inc.php');

#**********************************************************************************#


                #******************************************#
                #********** INITIALIZE VARIABLES **********#
                #******************************************#

                $errorLogin = null;		// Fehler Ausgabe beim Login
				$loginInfo 	= false; 	// Info, ob user eingelogt ist

				$filterID 	= null;		// Kategorien Filter

#**********************************************************************************#


                #****************************************#
                #********** PROCESS FORM LOGIN **********#
                #****************************************#

// if(DEBUG_V)	    echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")<i>:<br>\n";					
// if(DEBUG_V)	    print_r($_GET);					
// if(DEBUG_V)	    echo "</pre>";


				// Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['formLogin']) === true ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'Login' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 2 FORM: Werte auslesen, entschärfen, DEBUG-Ausgabe
if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    $email      	= cleanString($_POST['email']);
                    $password   	= cleanString($_POST['password']);
					
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$email: $email <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$password: $password <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 3 FORM: Validieren der Formularwerte
if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    $errorEmail     = validateEmail($email);
                    $errorPassword  = checkInputString($password);

                    #********** FINAL FORM VALIDATION **********#
                    if( $errorEmail !== null or $errorPassword !== null ) {
                        
if (DEBUG)              echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: Email/Password enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";
                        $errorLogin = 'Diese Logindaten sind ungültig!';
                    } else {
                        
if (DEBUG)              echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Logindaten sind formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";
                        
                        // Schritt 4 FORM: Daten weiterverarbeiten

                        #**********************************#
                        #********** DB OPERATION **********#
                        #**********************************#

                        // Schritt 1 DB: DB-Verbindung herstellen
                        $PDO = dbConnect();
if(DEBUG)	    		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese Logindaten aus DB... <i>(" . basename(__FILE__) . ")</i></p>\n";

                        $sql = 'SELECT * FROM users
                                WHERE userEmail = :email';
                        $params = ['email' => $email];

                        try {
							// Schritt 2 DB: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							// Schritt 3 DB: SQL-Statement ausführen
							$PDOStatement->execute($params);
							
						} catch(PDOException $error) {
if(DEBUG) 				    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}

                        // Schritt 4 DB: Daten weiterverarbeiten
                        $dataSet = $PDOStatement->fetch(PDO::FETCH_ASSOC);
                        
                        // DB-Verbindung schließen
if(DEBUG)			    echo "<p class='debugDB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
						unset($PDO);

if(DEBUG_V)			    echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_V)			    print_r($dataSet);					
if(DEBUG_V)             echo "</pre>";

                        #********** 1. VALIDATE ACCOUNT NAME **********#
if (DEBUG)              echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validiere Accountnamen... <i>(" . basename(__FILE__) . ")</i></p>\n";

						if( $dataSet === false ) {
							//Fehlerfall
if(DEBUG)					echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Der User '$email' existiert nicht in der DB'! <i>(" . basename(__FILE__) . ")</i></p>\n";							
							$errorLogin = 'Diese Logindaten sind ungültig!';
						
						} else {
                            // Erfolgsfall
if(DEBUG)				    echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Der User '$email' wurde in der DB gefunden. <i>(" . basename(__FILE__) . ")</i></p>\n";		

                            #********** 2. VALIDATE PASSWORD **********#
if(DEBUG)				    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Validiere Password... <i>(" . basename(__FILE__) . ")</i></p>\n";

                            if( password_verify($password, $dataSet['userPassword']) === false ) {
								// Fehlerfall: Passwort stimmt nicht
if(DEBUG)					    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das eingegebene Passwort stimmt NICHT mit dem Passwort aus der DB überein! <i>(" . basename(__FILE__) . ")</i></p>\n";								
								$errorLogin = 'Diese Logindaten sind ungültig!';
								
							}else {
                                // Erfolgsfall: Passwort stimmt
if(DEBUG)   					echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das eingegebene Passwort stimmt mit dem Passwort aus der DB überein. <i>(" . basename(__FILE__) . ")</i></p>\n";				
			
                                #********** 3. PROCESS LOGIN **********#
if(DEBUG)						echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Login wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";

                                #********** PREPARE SESSION **********#
								$loginInfo = true;
                                session_name('projektblog');

                                #********** START SESSION **********#
								session_start();

                                #********** SAVE USER DATA INTO SESSION FILE **********#
                                $_SESSION['IP'] 		= $_SERVER['REMOTE_ADDR'];
                                $_SESSION['ID'] 		= $dataSet['userID'];
								$_SESSION['firstName'] 	= $dataSet['userFirstName'];
								$_SESSION['lastName']	= $dataSet['userLastName'];

                                #********** REDIRECT TO profile.php **********#
                                header('LOCATION: dashboard.php');

                            } // 2. VALIDATE PASSWORD END

                        } // 1. VALIDATE ACCOUNT NAME END

                    } // FINAL FORM VALIDATION END

                } // PROCESS FORM LOGIN END

#**********************************************************************************#


				#********************************************#
				#********** PROCESS URL PARAMETERS **********#
				#********************************************#

// if(DEBUG_V)		echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
// if(DEBUG_V)		print_r($_GET);					
// if(DEBUG_V)		echo "</pre>";

				// Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde
				if( isset($_GET['action']) === true ) {
if(DEBUG)		echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";										
					
					// Schritt 2 URL: Auslesen, entschärfen und Debug-Ausgabe der übergebenen Parameter-Werte
					$action 	= cleanString($_GET['action']);
if(DEBUG_V)			echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";

					// Schritt 3 URL: i.d.R. je nach übergebenem Parameterwert verzweigen
					if( $action == 'logout' ) {

						#********** LOGOUT **********#
						$loginInfo = false;

					} elseif ( $action == 'loggedin' ) {
						// Eingelogt bei dashboard
						$loginInfo = true;
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: Ausgelogt \$loginInfo: $loginInfo <i>(" . basename(__FILE__) . ")</i></p>\n";

					} elseif( $action !== '' ) {
						// Gefiltered Katagerien
						$filterID = $action;
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$filterID: $filterID <i>(" . basename(__FILE__) . ")</i></p>\n";

					} else {
						// Kein Filter, alle Enträge
						$filterID = null;
if(DEBUG_V)				echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$filterID: $filterID <i>(" . basename(__FILE__) . ")</i></p>\n";

					}
				} // PROCESS URL PARAMETERS END

#**********************************************************************************#


				#**********************************#
				#********** DB OPERATION **********#
				#**********************************#

				// Schritt 1 DB: Verbindung zur Datenbank aufbauen
				$PDO = dbConnect();

				#******************** CHECK SELECTED CATEGORY OF BLOGS ********************#
				if( $filterID !== null ) {
					// Es wurde einen Filter ausgewählt
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese gefilterte BLOG-EINTRÄGE aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$sql = 'SELECT * FROM blogs
							INNER JOIN categories USING(catID)
							INNER JOIN users USING(userID)
							WHERE catID = :filterID
							ORDER BY blogDate DESC';
					$params = [ 'filterID' => $filterID ];

				} else {
					// Es wurde keinen Filter ausgewählt
if(DEBUG)			echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese alle BLOG-EINTRÄGE aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";

					$sql = 'SELECT * FROM blogs
							INNER JOIN categories USING(catID)
							INNER JOIN users USING(userID)
							ORDER BY blogDate DESC'
							;
					$params = [];
					
				} // CHECK SELECTED CATEGORY OF BLOGS END

				try {
					// Schritt 2 DB: SQL-Statement vorbereiten
					$PDOStatement = $PDO->prepare($sql);

					// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter mit Werten füllen
					$PDOStatement->execute($params);
							
				} catch(PDOException $error) {
if(DEBUG) 			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
					$dbError = 'Fehler beim Zugriff auf die Datenbank!';
				}

				// Schritt 4 DB: Weiterverarbeitung der Daten
				$resultBlogArr = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
				// DB-Verbindung schließen
if(DEBUG)		echo "<p class='debugDB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
				unset($PDO);

// if(DEBUG_V)	    echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")<i>:<br>\n";					
// if(DEBUG_V)	    print_r($resultBlogArr);					
// if(DEBUG_V)	    echo "</pre>";

#**********************************************************************************#


				#**********************************#
				#********** DB OPERATION **********#
				#**********************************#

				// Schritt 1 DB: Verbindung zur Datenbank aufbauen
				$PDO = dbConnect();
if(DEBUG)		echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese KATEGORIE aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";

				$sql = 'SELECT * FROM categories ORDER BY catLabel';
				$params = [];

				try {
					// Schritt 2 DB: SQL-Statement vorbereiten
					$PDOStatement = $PDO->prepare($sql);		
					// Schritt 3 DB: SQL-Statement ausführen
					$PDOStatement->execute($params);
							
				} catch(PDOException $error) {
if(DEBUG) 			echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
					$dbError = 'Fehler beim Zugriff auf die Datenbank!';
				}

				// Schritt 4 DB: Weiterverarbeitung der Daten
				$resultCatArr= $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
				// DB-Verbindung schließen
if(DEBUG)		echo "<p class='debugDB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
				unset($PDO);

#**********************************************************************************#
?>


<!doctype html>

<html>

<head>
	<meta charset="utf-8">
	<title>PHP-Projekt Blog</title>

	<link rel="stylesheet" href="./css/main.css">
	<link rel="stylesheet" href="./css/debug.css">
</head>

<body>

	<!-- -------- PAGE HEADER START -------- -->
	<br>
	<header class="fright loginheader">

		<!-- -------- LOGIN FORM START -------- -->

		<?php if( $loginInfo === false ): ?>
			<form action="" method="POST">
				<input type="hidden" name="formLogin">
				<span class='error'><?php echo $errorLogin ?></span><br>
				<input class="short" type="text" name="email" placeholder="Email">
				<input class="short" type="password" name="password" placeholder="Passwort">
				<input class="short" type="submit" value="Login">
			</form>
		<?php endif ?>

		<!-- -------- LOGIN FORM END -------- -->



		<!-- -------- LOGGEDIN INFO START -------- -->

		<?php if( $loginInfo === true ): ?>
			<p><a href="dashboard.php">Eintag anlegen</a></p>
			<p><a href="dashboard.php?action=logout"><< logout</a></p>
		<?php endif ?>

		<!-- -------- LOGGEDIN INFO END -------- -->

	</header>
	<div class="clearer"></div>

	<!-- -------- PAGE HEADER END -------- -->

	<br>
	<br>
	<hr>

	<!-- -------- PAGE CONTENT START -------- -->

	<h1>PHP-Projekt Blog</h1>

	<p>
		<a href="index.php">Alle Einträge anzeigen</a>
	</p>

	<div class="container">

		<!-- -------- BLOGS CONTENT START -------- -->

		<main>
			<?php foreach ($resultBlogArr as $item): ?>
				<fieldset class="blog-content">
					<p class="fright">Kategorie: <?php echo $item['catLabel'] ?></p>
					<br>
					<h3><?php echo $item['blogHeadline'] ?></h3>
					<h5>
						<?php echo $item['userFirstName'] ?> 
						<?php echo $item['userLastName'] ?> 
						(<?php echo $item['userCity'] ?>) 
						schrieb am <?php echo isoToEuDateTime($item['blogDate'])['date'] ?>
						um <?php echo isoToEuDateTime($item['blogDate'])['time']  ?> Uhr
					</h5>
					<?php if ( $item['blogImagePath'] !== null ): ?>
						<img src="<?php echo $item['blogImagePath'] ?>" alt="" class="<?php echo $item['blogImageAlignment'] ?>">
					<?php endif ?>
					<p><?php echo nl2br($item['blogContent'], false) ?></p>
				</fieldset>
			<?php endforeach ?>
		</main>

		<!-- -------- BLOGS CONTENT END -------- -->



		<!-- -------- SELECT CATEGORY END -------- -->

		<aside>
			<fieldset class="blog-category">
				<?php foreach ($resultCatArr as $item): ?>
					<h2 class=""><a href="?action=<?php echo $item['catID'] ?>"><?php echo $item['catLabel'] ?></a></h2>
				<?php endforeach ?>
			</fieldset>
	
		</aside>

		<!-- -------- SELECT CATEGORY END -------- -->

	</div>

	<!-- -------- PAGE CONTENT END -------- -->


</body>

</html>