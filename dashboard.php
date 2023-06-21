<?php

#**********************************************************************************#


                #****************************************#
                #********** PAGE CONFIGURATION **********#
                #****************************************#

                require_once('./include/config.inc.php');
                require_once('./include/form.inc.php');
                require_once('./include/db.inc.php');

#**********************************************************************************#


                #****************************************#
                #********** SECURE PAGE ACCESS **********#
                #****************************************#

                session_name('projektblog');
                session_start();

// if(DEBUG_V)	echo "<pre class='debugAuth value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
// if(DEBUG_V)	print_r($_SESSION);					
// if(DEBUG_V)	echo "</pre>";

                #*******************************************#
                #********** CHECK FOR VALID LOGIN **********#
                #*******************************************#

                if( isset($_SESSION['ID']) === false or $_SESSION['IP'] !== $_SERVER['REMOTE_ADDR']) {
                    #********* NO VALID LOGIN **********#
if(DEBUG)   		echo "<p class='debugAuth err'><b>Line " . __LINE__ . "</b>: Login konnte nicht verifiziert werden! <i>(" . basename(__FILE__) . ")</i></p>\n";

                    #********** DENY PAGE ACCESS **********#
                    session_destroy();
                    header('LOCATION: index.php');
                    exit;

                    #********** VALID LOGIN **********#
                } else {
if(DEBUG)		    echo "<p class='debugAuth ok'><b>Line " . __LINE__ . "</b>: Login erfolgreich verifiziert. <i>(" . basename(__FILE__) . ")</i></p>\n";	

                    session_regenerate_id(true);
                    $userID         = $_SESSION['ID'];
                    $userFirstName  = $_SESSION['firstName'];
                    $userLastName   = $_SESSION['lastName'];

                } // SECURE PAGE ACCESS END

#**********************************************************************************#


                #******************************************#
                #********** INITIALIZE VARIABLES **********#
                #******************************************#

                $catID                      = null;
                $catLabel                   = null;

                $errorCategory              = null;

                $blogHeadline               = null;
                $blogImagePath              = null;
                $blogImageAlignment         = null;
                $blogContent                = null;

                $errorBlogHeadline          = null;
                $errorBlogImagePath         = null;
                $errorBlogImageAlignment    = null;
                $errorBlogContent           = null;
                $errorCatID                 = null;
                $errorUserID                = null;

                $errorImageUpload           = null;


#**********************************************************************************#


                #***********************************************#
                #************ PROCESS FORM CATEGORY ************#
                #***********************************************#

// if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
// if(DEBUG_V)	print_r($_POST);					
// if(DEBUG_V)	echo "</pre>";

                // Schritt 1 FORM
				if( isset($_POST['category']) === true ) {
if(DEBUG)		    echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'KATEGORIE' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";


                    // Schritt 2 FORM
if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    $catLabel       = cleanString($_POST['addCategory']);
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$catLabel: $catLabel <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 3 FORM
if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwert 'Category' wird validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";
                    $errorCategory  = checkInputString($catLabel, minLength:2);

                    #********** FINAL FORM VALIDATION **********#
                    if ( $errorCategory !== null) {
                        //Fehlerfall
if(DEBUG)			    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular 'KATEGORIE' enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";	
                    } else {
                        //Erfolgsfall
if(DEBUG)			    echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular 'KATEGORIE' ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";


                        #***********************************#
                        #********** DB OPERATIONS **********#
                        #***********************************#

                        #********** CHECK IF CATEGORY SAVED ALREADY IN DB **********#

                        // Schrit 1 DB: Verbindung zur Datenbank aufbauen
                        $PDO    = dbConnect();
                        $sql    = 'SELECT COUNT(catLabel) FROM categories
                                    WHERE catLabel = :catLabel';
                        $params = ['catLabel' => $catLabel];

                        try {
							// Schritt 2 DB: SQL-Statement vorbereiten
							$PDOStatement = $PDO->prepare($sql);
							// Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter mit Werten füllen
							$PDOStatement->execute($params);
							
						} catch(PDOException $error) {
if(DEBUG) 				    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
							$dbError = 'Fehler beim Zugriff auf die Datenbank!';
						}

                        // Schritt 4 DB: Weiterverarbeitung der Daten
                        $count = $PDOStatement->fetchColumn();
if(DEBUG_V)			    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$count: $count <i>(" . basename(__FILE__) . ")</i></p>\n";

                        if( $count !== 0 ) {
							
if(DEBUG)				    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Die Kategorie '$catLabel' ist bereits in der DB geschpeichert! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							$errorCategory = 'Die eingegebene Kategorie existiert bereits';
							
						} else {
if(DEBUG)	    			echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Die Kategorie '$catLabel' ist noch nicht in der DB registriert. <i>(" . basename(__FILE__) . ")</i></p>\n";				
                            $errorCategory = '';

                            #***********************************#
                            #********** DB OPERATIONS **********#
                            #***********************************#

                            // Schrit 1 DB: Verbindung zur Datenbank aufbauen
                            $PDO    = dbConnect();

                            $sql    = 'INSERT INTO categories(catLabel)
                                        VALUES (:catLabel)';

                            $params = ['catLabel' => $catLabel];

                            try {
                            // Schritt 2 DB: SQL-Statement vorbereiten
                            $PDOStatement = $PDO->prepare($sql);
                            // Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter mit Werten füllen
                            $PDOStatement->execute($params);

                            } catch (PDOException $error) {
    if (DEBUG)                  echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                                $dbError = 'Fehler beim Zugriff auf die Datenbank!';
                            }   

                            // Schritt 4 DB: Weiterverarbeitung der Daten
                            $rowCount = $PDOStatement->rowCount();
if(DEBUG_V)					echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

                            if ( $rowCount !== 1 ) {
                                // Fehlerfall
if(DEBUG)						echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern des 'KATEGORIE' Datensatzes! <i>(" . basename(__FILE__) . ")</i></p>\n";
                                $dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';

                            } else {
                                // Erfolgsfall
                                $newCatID = $PDO->lastInsertId();
if(DEBUG)						echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Neue 'KATEGORIE' erfolgreich unter ID$newCatID gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";
						    	$dbSuccess = 'Das neue Kategorie wurde erfolgreich gespeichert.';
						    }

						    // DB-Verbindung schließen
if(DEBUG)			        echo "<p class='debugDB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
						    unset($PDO);

                        } // CHECK IF CATEGORY SAVED ALREADY IN DB END

                    } // FINAL FORM VALIDATION END
            
                } // PROCESS FORM CATEGORY END

#**********************************************************************************#


                #***************************************************#
                #************ PROCESS FORM BLOG-CONTENT ************#
                #***************************************************#

// if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
// if(DEBUG_V)	print_r($_POST);					
// if(DEBUG_V)	echo "</pre>";

                // Schritt 1 FORM: Prüfen, ob Formular abgeschickt wurde
				if( isset($_POST['blogContent']) === true ) {
if(DEBUG)		    echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: Formular 'BLOG-EINTRAG' wurde abgeschickt. <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 2 FORM: Auslesen, entschärfen und Debug-Ausgabe der übergebenen Formularwerte
if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Werte werden ausgelesen und entschärft... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    $blogHeadline           = cleanString( $_POST['title'] );
                    $blogImageAlignment     = cleanString( $_POST['bildPosition'] );
                    $blogContent            = cleanString( $_POST['text'] );
                    $catID                  = cleanString( $_POST['chooseCategory'] );
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$blogHeadline: $blogHeadline <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$blogImageAlignment: $blogImageAlignment <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$blogContent: $blogContent <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$catID: $catID <i>(" . basename(__FILE__) . ")</i></p>\n";

                    // Schritt 3 FORM: Feldvalidierung und abschließende Formularprüfung
if(DEBUG)		    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Feldwerte werden validiert... <i>(" . basename(__FILE__) . ")</i></p>\n";

                    $errorBlogHeadline       = checkInputString( $blogHeadline );
                    $errorBlogImageAlignment = checkInputString( $blogImageAlignment,   mandatory:false );
                    $errorBlogContent        = checkInputString( $blogContent,          maxLength:5000 );
                    $errorCatID              = checkInputString( $catID );

                    #********** FINAL FORM VALIDATION I **********#
                    if ( $errorBlogHeadline     !== null or $errorBlogImagePath     !== null or $errorBlogImageAlignment !== null or
                         $errorBlogContent      !== null or $errorCatID             !== null ) {

if(DEBUG)			    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: Das Formular 'BLOG-EINTRAG' enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";	
                    } else {
if(DEBUG)			    echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Das Formular 'BLOG-EINTRAG' ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";

                            #**********************************#
                            #********** IMAGE UPLOAD **********#
                            #**********************************#

// if(DEBUG_V)	        		echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
// if(DEBUG_V)			        print_r($_FILES);					
// if(DEBUG_V)                 echo "</pre>";

                            #********** CHECK IF IMAGE UPLOAD IS ACTIVE **********#
						    if( $_FILES['picture']['tmp_name'] === '' ) {
                                // Es wurde keinen Bild ausgewählt
if(DEBUG)				        echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Image Upload INACTIVE. <i>(" . basename(__FILE__) . ")</i></p>\n";	

							} else {
                                // Es wurde einen Bild ausgewählt
if(DEBUG)				        echo "<p class='debug hint'><b>Line " . __LINE__ . "</b>: Image Upload ACTIVE. <i>(" . basename(__FILE__) . ")</i></p>\n";				
                                $imageUploadReturnArr = imageUpload( $_FILES['picture']['tmp_name'] );

// if(DEBUG_V)				        echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
// if(DEBUG_V)				        print_r($imageUploadReturnArr);					
// if(DEBUG_V)				        echo "</pre>";

                                #********** VALIDATE IMAGE UPLOAD **********#
							    if( $imageUploadReturnArr['imageError'] !== NULL ) {
                                    // Fehlerfall: Bild wurde nicht gespeichert
if(DEBUG)					        echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Bild-Upload: <i>'$imageUploadReturnArr[imageError]'</i> <i>(" . basename(__FILE__) . ")</i></p>\n";				
								    $errorImageUpload = $imageUploadReturnArr['imageError'];
								
							    } else {
                                    // Erfolgsfall
if(DEBUG)					        echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Bild erfolgreich unter <i>'$imageUploadReturnArr[imagePath]'</i> gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";
                                    $blogImagePath = $imageUploadReturnArr['imagePath'];

                                } // VALIDATE IMAGE UPLOAD END

                            } // IMAGE UPLOAD END

                                #******************** FINAL FORM VALIDATION II (IMAGE UPLOAD) ********************#
           						if( $errorImageUpload !== NULL ) {
                                    // Fehlerfall
if(DEBUG)				            echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: IMAGE UPLOAD VALIDATION: Das Formular 'BLOG-EINTRAG' enthält noch Fehler! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
						        } else {
                                    // Erfolgsfall
if(DEBUG)				            echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: IMAGE UPLOAD VALIDATION: Das Formular 'BLOG-EINTRAG' ist formal fehlerfrei. <i>(" . basename(__FILE__) . ")</i></p>\n";				

                                    // Schritt 4 FORM: Daten weiterverarbeiten

                                    #***********************************#
                                    #********** DB OPERATIONS **********#
                                    #***********************************#

                                    // Schrit 1 DB: Verbindung zur Datenbank aufbauen
                                    $PDO    = dbConnect();

                                    $sql    = 'INSERT INTO blogs(blogHeadline, blogImagePath, blogImageAlignment, blogContent, catID, userID)
                                                VALUES (:blogHeadline, :blogImagePath, :blogImageAlignment, :blogContent, :catID, :userID)';

                                    $params = [ 'blogHeadline'          => $blogHeadline,
                                                'blogImagePath'         => $blogImagePath,
                                                'blogImageAlignment'    => $blogImageAlignment,
                                                'blogContent'           => $blogContent,
                                                'catID'                 => $catID,
                                                'userID'                => $userID
                                            ];
                                    
                                    try {
                                    // Schritt 2 DB: SQL-Statement vorbereiten
                                    $PDOStatement = $PDO->prepare($sql);
                                    // Schritt 3 DB: SQL-Statement ausführen und ggf. Platzhalter mit Werten füllen
                                    $PDOStatement->execute($params);

                                    } catch (PDOException $error) {
if (DEBUG)                          echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";
                                        $dbError = 'Fehler beim Zugriff auf die Datenbank!';
                                    }  
                                    // Schritt 4 DB: Weiterverarbeitung der Daten
                                    $rowCount = $PDOStatement->rowCount();
if(DEBUG_V)					        echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$rowCount: $rowCount <i>(" . basename(__FILE__) . ")</i></p>\n";

                                    if ( $rowCount !== 1 ) {
if(DEBUG)						    echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER beim Speichern des Blogdatensatzes! <i>(" . basename(__FILE__) . ")</i></p>\n";
                                    $dbError = 'Es ist ein Fehler aufgetreten! Bitte versuchen Sie es später noch einmal.';

                                    } else {
                                        $newBlogID = $PDO->lastInsertId();
if(DEBUG)						        echo "<p class='debug ok'><b>Line " . __LINE__ . "</b>: Neuer BLOG-EINTRAG erfolgreich unter ID$newBlogID gespeichert. <i>(" . basename(__FILE__) . ")</i></p>\n";
						    	        $dbSuccess = 'Der neuen Blog-Eintrag wurde erfolgreich gespeichert.';
						    }

						            // DB-Verbindung schließen
if(DEBUG)			                echo "<p class='debugDB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
						            unset($PDO);

                                    $blogHeadline = $blogImagePath = $blogImageAlignment = $blogContent = null;

                                } // FINAL FORM VALIDATION II (IMAGE UPLOAD) END
                    
                        } // FINAL FORM VALIDATION I END

                } // PROCESS FORM BLOG-CONTENT END
#**********************************************************************************#


                #**************************************************#
                #********** FETCH CATEGORY FROM DATABASE **********#
                #**************************************************#

if (DEBUG)      echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Lese 'KATEGORIEN' aus DB aus... <i>(" . basename(__FILE__) . ")</i></p>\n";
                // Schritt 1 DB: DB-Verbindung
                $PDO = dbConnect();
                $sql = 'SELECT * FROM categories';
                $params = [];

                try {
					// Schritt 2 DB: SQL-Statement
					$PDOStatement = $PDO->prepare($sql);
					// Schritt 3 DB: SQL-Statement ausführen
					$PDOStatement->execute($params);
					
				} catch(PDOException $error) {
if(DEBUG) 		echo "<p class='debug err'><b>Line " . __LINE__ . "</b>: FEHLER: " . $error->GetMessage() . "<i>(" . basename(__FILE__) . ")</i></p>\n";										
					$dbError = 'Fehler beim Zugriff auf die Datenbank!';
				}

                $resultArr = $PDOStatement->fetchAll(PDO::FETCH_ASSOC);
                // DB-Verbindung schließen
if(DEBUG)	    echo "<p class='debugDB'><b>Line " . __LINE__ . "</b>: DB-Verbindung wird geschlossen. <i>(" . basename(__FILE__) . ")</i></p>\n";		
				unset($PDO);

// if(DEBUG_V)	echo "<pre class='debug value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
// if(DEBUG_V)	print_r($resultArr);					
// if(DEBUG_V)	echo "</pre>";


#**********************************************************************************#


                #********************************************#
                #********** PROCESS URL PARAMETERS **********#
                #********************************************#

                // Schritt 1 URL: Prüfen, ob URL-Parameter übergeben wurde
                if( isset($_GET['action']) === true ) {
if(DEBUG)		    echo "<p class='debug'>🧻 <b>Line " . __LINE__ . "</b>: URL-Parameter 'action' wurde übergeben. <i>(" . basename(__FILE__) . ")</i></p>\n";										
					
					// Schritt 2 URL: Auslesen, entschärfen und Debug-Ausgabe der übergebenen Parameter-Werte
                    $action = cleanString( $_GET['action'] );
if(DEBUG_V)		    echo "<p class='debug value'><b>Line " . __LINE__ . "</b>: \$action: $action <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					// Schritt 3 URL: i.d.R. je nach übergebenem Parameterwert verzweigen

                    #********** LOGOUT **********#
                    if( $action === 'logout' ) {
if(DEBUG)			    echo "<p class='debug'>📑 <b>Line " . __LINE__ . "</b>: Logout wird durchgeführt... <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// Schritt 4 URL: Weiterverarbeitung

                        #********** PROCESS LOGOUT **********#
                        // 1. Session Datei löschen
						session_destroy();

                        // 2. Umleiten auf öffentliche Seite
						header('LOCATION: index.php');

                        // 3. Fallback
						exit;

                    } // LOGOUT END

                } // PROCESS URL PARAMETERS END

#**********************************************************************************#


?>



<!doctype html>

<html>

<head>
	<meta charset="utf-8">
	<title>Benutzerverwaltung | Profile</title>

	<link rel="stylesheet" href="./css/main.css">
	<link rel="stylesheet" href="./css/pageElements.css">
	<link rel="stylesheet" href="./css/debug.css">
</head>

<body>

	<!-- -------- PAGE HEADER -------- -->

	<header class="fright loginheader">
        <p class="">
            <a href="index.php?action=loggedin">frontend</a>
        </p>
        
		<p class="fright">
            <a href="?action=logout"><< Logout</a>
		</p>
	</header>
	<div class="clearer"></div>
	<hr>

	<!-- -------- PAGE HEADER END -------- -->


	<br>
	

	<!-- ---------- USER MESSAGES START ---------- -->

	<?php if( isset($dbError) === true ): ?>
		<h3 class="error"><?php echo $dbError ?></h3>
	<?php elseif( isset($dbInfo) === true ): ?>
		<h3 class="info"><?php echo $dbInfo ?></h3>
	<?php elseif( isset($dbSuccess) === true ): ?>
		<h3 class="success"><?php echo $dbSuccess ?></h3>
	<?php endif ?>

	<!-- ---------- USER MESSAGES END ---------- -->

	
	<br>


	<!-- ---------- PROFILE INFO START ---------- -->

	<div>
		<h1>Benutzerverwaltung | Profile</h1>
		<p>Aktiver Benutzer: <b><?php echo $userFirstName?>, <?php echo $userLastName ?></b></p>
    </div>

    <!-- ---------- PROFILE INFO END ---------- -->



    <!-- -------- FORM BLOG CONTENT START -------- -->

    <div class="container">
        <section>
            <h3>Neuen Blog-Eintrag verfassen</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="blogContent">

                <!-- -------- SELECT CATEGORIE START--------  -->

                <select name="chooseCategory">
                    <?php foreach ($resultArr as $item): ?>
					    <option value="<?php echo $item['catID'] ?>" <?php echo $item['catID'] == $catID ? 'selected' : '' ?>><?php echo $item['catLabel'] ?></option>
                    <?php endforeach ?>
				</select>

				<!-- -------- SELECT CATEGORIE END  --------  -->

				<br>

                 <!-- -------- TITLE START--------  -->

				<br><span class="error"><?php echo $errorBlogHeadline ?></span><br>
				<input type="text" name="title" placeholder="Überschrift" value="<?php echo $blogHeadline ?>"><br>

                 <!-- -------- TITLE END--------  -->


				<!-- -------- FILE UPLOAD START--------  -->

				<h5>Bild hochladen:</h5>
                <span class="error"><?php echo $errorImageUpload ?>
                </span>
                <div class="bild-upload">
                    <input type="file" name="picture">
                    <select name="bildPosition" id="">
                        <option value="fleft">align left</option>
                        <option value="fright">align right</option>
                    </select>
                </div>

                <!-- -------- FILE UPLOAD END -------- -->
                
                <div class="clearer"></div>
                
                <!-- -------- BLOG CONTENT START--------  -->

                <br><span class="error"><?php echo $errorBlogContent ?></span><br>
				<textarea name="text" placeholder="Text..."><?php echo $blogContent ?></textarea>

                <!-- -------- BLOG CONTENT END--------  -->

				<input type="submit" value="Veröffentlichen">
			</form>

		</section>
		<!-- -------- FORM BLOG CONTENT END -------- -->




		<!-- -------- FORM CATEGORIES START -------- -->
		<section>
			<h3>Neue Kategorie anlegen</h3>

			<form method="POST">
				<input type="hidden" name="category">
                <br><span class="error"><?php echo $errorCategory ?></span><br>
				<input type="text" name="addCategory" placeholder="Name der Kategorie" value="<?php  echo $errorCategory !== '' ? $catLabel : '' ?>">
                <span class="marker">*</span><br>
                <input type="submit" value="Neue Kategorie anlegen">
            </form>

        </section>
        <!-- -------- FORM CATEGORIES END -------- -->

    </div>


    <br>
    <br>

</body>

</html>