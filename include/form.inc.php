<?php
#**********************************************************************************#


				#**********************************#
				#********** CLEAN STRING **********#
				#**********************************#
				
				
				/**
				*
				*	Ersetzt potentiell gefährliche Zeichen (< > " ' &) eines übergebenen Werts
				*	durch HTML-Entities und entfernt alle Whitespaces vor und nach dem Wert.
				*
				*	@param	String|Int|Float		$value		Der zu übergebene Wert
				*
				*	@return	String|NULL							Der entschärfte und bereinigte String
				*															NULL bei übergebenem NULL
				*
				*/
				function cleanString($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debugCleanString'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
					/*
						Da die Übergabe von NULL an PHP-Funktionen deprecated (künftig
						nicht mehr erlaubt) ist, muss gff. vor Funktionsaufrufen auf
						NULL-Werte geprüft werden.
					*/
					if( $value !== NULL ) {
						/*
							SCHUTZ GEGEN EINSCHLEUSUNG UNERWÜNSCHTEN CODES:
							Damit so etwas nicht passiert: <script>alert("HACK!")</script>
							muss der empfangene String ZWINGEND entschärft werden!
							htmlspecialchars() wandelt potentiell gefährliche Steuerzeichen wie
							< > " & in HTML-Code um (&lt; &gt; &quot; &amp;).
							
							Der Parameter ENT_QUOTES wandelt zusätzlich einfache ' in &apos; um.
							Der Parameter ENT_HTML5 sorgt dafür, dass der generierte HTML-Code HTML5-konform ist.
							
							Der 1. optionale Parameter regelt die zugrundeliegende Zeichencodierung 
							(NULL=Zeichencodierung wird vom Webserver übernommen)
							
							Der 2. optionale Parameter bestimmt die Zeichenkodierung
							
							Der 3. optionale Parameter regelt, ob bereits vorhandene HTML-Entities erneut entschärft werden
							(false=keine doppelte Entschärfung)
						*/
						$value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, double_encode:false);
						
						/*
							trim() entfernt VOR und NACH einem String (aber nicht mitten drin) 
							sämtliche sog. Whitespaces (Leerzeichen, Tabs, Zeilenumbrüche)
						*/
						$value = trim($value);
					}
					
					return $value;
					#********** LOCAL SCOPE END **********#
				}


#**********************************************************************************#

				
				#****************************************#
				#********** CHECK INPUT STRING **********#
				#****************************************#
				
				
				/**
				*
				*	Prüft einen übergebenen String auf Mindestlänge und Maximallänge sowie optional 
				* 	zusätzlich auf Leerstring.
				*	Generiert Fehlermeldung bei Leerstring oder ungültiger Länge
				*
				*	@param	NULL|String		$value							Der zu übergebende String
				*	@param	Integer			$minLength=INPUT_MIN_LENGTH		Die zu prüfende Mindestlänge															
				*	@param	Integer			$maxLength=INPUT_MAX_LENGTH		Die zu prüfende Maximallänge					
				*	@param	Bool			$mandatory=true					Angabe zu Pflichteingabe
				*
				*	@return	String|NULL										Fehlermeldung | ansonsten NULL
				*
				*/
				function checkInputString($value, $mandatory=true, $minLength=INPUT_MIN_LENGTH, $maxLength=INPUT_MAX_LENGTH ) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debugCheckInputString'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value | [$minLength|$maxLength] | mandatory:$mandatory') <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
					
					#********** MANDATORY CHECK **********#
					// OPTIONAL: Wenn $mandatory === true: Prüfen auf Leerstring oder NULL
					if( $mandatory === true AND ($value === '' OR $value === NULL) ) {						
						// Fehlerfall
						return 'Dies ist ein Pflichtfeld!';
					
					
					#********** MAXIMUM LENGTH CHECK **********#
					/*
						Da die Felder in der Datenbank oftmals eine Längenbegrenzung besitzen,
						die Datenbank aber bei Überschreiten dieser Grenze keine Fehlermeldung
						ausgibt, sondern alles, das über diese Grenze hinausgeht, stillschweigend 
						abschneidet, muss vorher eine Prüfung auf diese Maximallänge durchgeführt 
						werden. Nur so kann dem User auch eine entsprechende Fehlermeldung ausgegeben
						werden.
					*/
					/*
						Seit PHP 8.1... dürfen keine NULL-Werte mehr an PHP-Funktionen übergeben werden.
					*/
					} elseif( $value !== NULL AND mb_strlen($value) > $maxLength ) {
						// Fehlerfall
						return "Darf maximal $maxLength Zeichen lang sein!";
					
					
					#********** MINIMUM LENGTH CHECK **********#
					/*
						Es gibt Sonderfälle, bei denen eine Mindestlänge für einen Userinput
						vorgegeben ist, beispielsweise bei der Erstellung von Passwörtern.
						Damit nicht-Pflichtfelder aber auch weiterhin leer sein dürfen, muss
						die Mindestlänge als Standardwert mit 0 vorbelegt sein.
					*/
					/*
						Seit PHP 8.1... dürfen keine NULL-Werte mehr an PHP-Funktionen übergeben werden.
					*/
					} elseif( $value !== NULL AND mb_strlen($value) < $minLength ) {
						// Fehlerfall
						return "Muss mindestens $minLength Zeichen lang sein!";
					
					
					#********** INPUT STRING IS VALID **********#
					} else {
						// Erfolgsfall
						return NULL;
						
					}
					#********** LOCAL SCOPE END **********#
				}
				

#**********************************************************************************#


				#************************************#
				#********** VALIDATE EMAIL **********#
				#************************************#
				
				
				/**
				*
				*	Prüft einen übergebenen String auf Leerstring und Länge
				*	via checkInputString().
				*	Prüft den übergebenen String zusätzlich auf eine valide Email-Adresse.
				*	Generiert Fehlermeldung bei Leerstring oder ungültiger Länge 
				*	oder ungültiger Email-Adresse.
				*
				*	@param	String	$value							Der zu übergebende String
				*
				*	@return	String|NULL									Fehlermeldung | ansonsten NULL
				*
				*/
				function validateEmail($value) {
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debugValidateEmail'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$value') <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
					
					#********** MANDATORY CHECK **********#
					$error = checkInputString($value);
					if( $error !== NULL ) {
						// Fehlerfall
						return $error;
						
					
					#********** VALIDATE EMAIL ADDRESS FORMAT **********#
					} elseif( filter_var($value, FILTER_VALIDATE_EMAIL) === false ) {
						// Fehlerfall
						return 'Dies ist keine gültige Email-Adresse!';
					

					#********** EMAIL ADDRESS IS VALID **********#					
					} else {
						// Erfolgsfall
						return NULL;
					}
					#********** LOCAL SCOPE END **********#
				}


#**********************************************************************************#


				/**
				*
				*	Validiert ein auf den Server geladenes Bild, generiert einen unique Dateinamen
				*	sowie eine sichere Dateiendung und verschiebt das Bild in ein anzugebendes Zielverzeichnis.
				*	Validiert werden der aus dem Dateiheader ausgelesene MIME-Type, die aus dem Dateiheader
				*	ausgelesene Bildgröße in Pixeln sowie die auf Dateiebene ermittelte Dateigröße. 
				*	Der Dateiheader wird außerdem auf Plausibilität geprüft.
				*
				*	@param	String	$fileTemp													Der temporäre Pfad zum hochgeladenen Bild im Quarantäneverzeichnis
				*	@param	Integer	$imageMaxHeight				=IMAGE_MAX_HEIGHT				Die maximal erlaubte Bildhöhe in Pixeln
				*	@param	Integer	$imageMaxWidth				=IMAGE_MAX_WIDTH				Die maximal erlaubte Bildbreite in Pixeln				
				*	@param	Integer	$imageMinSize				=IMAGE_MIN_SIZE					Die minimal erlaubte Dateigröße in Bytes
				*	@param	Integer	$imageMaxSize				=IMAGE_MAX_SIZE					Die maximal erlaubte Dateigröße in Bytes
				*	@param	Array	$imageAllowedMimeTypes		=IMAGE_ALLOWED_MIME_TYPES		Whitelist der zulässigen MIME-Types mit den zugehörigen Dateiendungen
				*	@param	String	$imageUploadPath			=IMAGE_UPLOAD_PATH				Das Zielverzeichnis
				*
				*	@return	Array	{	'imagePath'	=>	String|NULL, 							Bei Erfolg der Speicherpfad zur Datei im Zielverzeichnis | bei Fehler NULL
				*						imageError'	=>	String|NULL}							Bei Erfolg NULL | Bei Fehler Fehlermeldung
				*
				*/
				function imageUpload(	$fileTemp,
										$imageMaxHeight 			= IMAGE_MAX_HEIGHT,
										$imageMaxWidth 				= IMAGE_MAX_WIDTH,
										$imageMinSize 				= IMAGE_MIN_SIZE,
										$imageMaxSize 				= IMAGE_MAX_SIZE,
										$imageAllowedMimeTypes 		= IMAGE_ALLOWED_MIME_TYPES,
										$imageUploadPath			= IMAGE_UPLOAD_PATH
									) 
				{
					#********** LOCAL SCOPE START **********#
if(DEBUG_F)		echo "<p class='debugImageUpload'>🌀 <b>Line " . __LINE__ . "</b>: Aufruf " . __FUNCTION__ . "('$fileTemp') <i>(" . basename(__FILE__) . ")</i></p>\n";	
					
					
					#***********************************************************************#
					#********** GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER **********#
					#***********************************************************************#
					
					/*
						Die Funktion getimagesize() liest den Dateiheader einern Bilddatei aus und 
						liefert bei gültigem MIME Type ('image/...') ein gemischtes Array zurück:
						
						[0] 				Bildbreite in PX 
						[1] 				Bildhöhe in PX 
						[3] 				Einen für das HTML <img>-Tag vorbereiteten String (width="480" height="532") 
						['bits']			Anzahl der Bits pro Kanal 
						['channels']	Anzahl der Farbkanäle (somit auch das Farbmodell: RGB=3, CMYK=4) 
						['mime'] 		MIME Type
						
						Bei ungültigem MIME Type (also nicht 'image/...') liefert getimagesize() false zurück
					*/
					$imageDataArray = @getImageSize($fileTemp);
/*					
if(DEBUG_F)		echo "<pre class='debugImageUpload value'>Line <b>" . __LINE__ . "</b> <i>(" . basename(__FILE__) . ")</i>:<br>\n";					
if(DEBUG_F)		print_r($imageDataArray);					
if(DEBUG_F)		echo "</pre>";					
*/					
					
					#********** CHECK FOR VALID MIME TYPE **********#
					if( $imageDataArray === false ) {
						// Fehlerfall (MIME TYPE IS NO VALID IMAGE TYPE)						
						/*
							Bildwerte auf NULL setzen, damit die Variablen für die nachfolgenden
							Validierungen existieren und zu korrekten Fehlermeldungen führen
						*/
						$imageWidth = $imageHeight = $imageMimeType = $fileSize = NULL;
					
					
					#********** FETCH FILE INFOS **********#
					} elseif( is_array($imageDataArray) === true ) {
						// Erfolgsfall (MIME TYPE IS VALID IMAGE TYPE)
						
						$imageWidth 	= cleanString( $imageDataArray[0] );			// image WIDTH via FILE HEADER
						$imageHeight 	= cleanString( $imageDataArray[1] );			// image HEIGHT via FILE HEADER
						$imageMimeType 	= cleanString( $imageDataArray['mime'] );		// image MIME TYPE via FILE HEADER
						$fileSize 		= fileSize($fileTemp);								// file size in bytes vie filesize()						
					}
					
if(DEBUG_F)		echo "<p class='debugImageUpload value'><b>Line " . __LINE__ . "</b>: \$imageWidth: $imageWidth px<i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debugImageUpload value'><b>Line " . __LINE__ . "</b>: \$imageHeight: $imageHeight px<i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debugImageUpload value'><b>Line " . __LINE__ . "</b>: \$imageMimeType: $imageMimeType <i>(" . basename(__FILE__) . ")</i></p>\n";
if(DEBUG_F)		echo "<p class='debugImageUpload value'><b>Line " . __LINE__ . "</b>: \$fileSize: " . round($fileSize/1024,2) . "kB <i>(" . basename(__FILE__) . ")</i></p>\n";
					
					// GATHER INFORMATION FOR IMAGE FILE VIA FILE HEADER
					#*******************************************************************#
					
					
					#**************************************#
					#********** IMAGE VALIDATION **********#
					#**************************************#
					
					// Whitelist mit erlaubten MIME TYPES
					// $imageAllowedMimeTypes = array('image/jpeg'=>'.jpg', 'image/jpg'=>'.jpg', 'image/gif'=>'.gif', 'image/png'=>'.png');
					
					
					#********** CHECK IF FILE HEADER IS PLAUSIBLE **********#
					if( $fileSize < $imageMinSize OR $imageWidth === NULL OR $imageHeight === NULL OR $imageMimeType === NULL ) {
						// 1. Fehlerfall: Potentiell verdächtiger Datei Header
						$errorMessage = 'Potentiell verdächtiger Dateiupload!';
					
					
					#********** CHECK FOR ALLOWED MIME TYPES **********#	
					/*
						Die Funktion in_array() prüft, ob eine übergebene Needle einem Wert (value) innerhalb 
						eines zu übergebenden Arrays entspricht.
						
						Die Funktion array_key_exists() prüft, ob eine übergebene Needle einem Index (key) innerhalb 
						eines zu übergebenden Arrays entspricht.
					*/
					} elseif( array_key_exists($imageMimeType, $imageAllowedMimeTypes) === false ) {
						// 2. Fehlerfall: Unerlaubter Bildtyp
						$errorMessage = 'Dies ist kein erlaubter Bildtyp!';
						
					
					#********** VALIDATE IMAGE HEIGHT **********#
					} elseif( $imageHeight > $imageMaxHeight ) {
						// 3. Fehlerfall: Bildhöhe zu groß
						$errorMessage = "Die Bildhöhe darf maximal $imageMaxHeight Pixel betragen!";
						
					
					#********** VALIDATE IMAGE WIDTH **********#
					} elseif( $imageWidth > $imageMaxWidth ) {
						// 4. Fehlerfall: Bildbreite zu groß
						$errorMessage = "Die Bildbreite darf maximal $imageMaxWidth Pixel betragen!";
					
					
					#********** VALIDATE FILE SIZE **********#	
					} elseif( $fileSize > $imageMaxSize ) {
						// 4. Fehlerfall: Dateigröße zu groß
						$errorMessage = "Die Dateigröße darf maximal $imageMaxSize kB betragen!";
						
					
					#********** ALL CHECKS ARE PASSED SUCCESSFULLY **********#
					} else {
						// Erfolgsfall
						$errorMessage = NULL;
						
						
					} // IMAGE VALIDATION END
					#*******************************************************************#
					
					
					#********** FINAL IMAGE VALIDATION **********#
					if( $errorMessage !== NULL ) {
						// Fehlerfall
if(DEBUG_F)			echo "<p class='debugImageUpload err'><b>Line " . __LINE__ . "</b>: Bildvalidierungsfehler: $errorMessage <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// Initialize $fileTarget
						$fileTarget = NULL;
						
					} else {
						// Erfolgsfall
if(DEBUG_F)			echo "<p class='debugImageUpload ok'><b>Line " . __LINE__ . "</b>: Die Bildvalidierung ergab keinen Fehler. <i>(" . basename(__FILE__) . ")</i></p>\n";				
						
						
						#**********************************************************#
						#********** PREPARE IMAGE FOR PERSISTANT STORAGE **********#
						#**********************************************************#						
						/*
							Da der Dateiname selbst Schadcode in Form von ungültigen oder versteckten Zeichen,
							doppelte Dateiendungen (dateiname.exe.jpg) etc. beinhalten kann, darüberhinaus ohnehin 
							sämtliche, nicht in einer URL erlaubten Sonderzeichen und Umlaute entfernt werden müssten 
							sollte der Dateiname aus Sicherheitsgründen komplett neu generiert werden.
							
							Hierbei muss außerdem bedacht werden, dass die jeweils generierten Dateinamen unique
							sein müssen, damit die Dateien sich bei gleichem Dateinamen nicht gegenseitig überschreiben.
						*/
						#********** GENERATE UNIQUE FILE NAME **********#
						/*
							- 	mt_rand() stellt die verbesserte Version der Funktion rand() dar und generiert 
								Zufallszahlen mit einer gleichmäßigeren Verteilung über das Wertesprektrum. Ohne zusätzliche
								Parameter werden Zahlenwerte zwischen 0 und dem höchstmöglichem von mt_rand() verarbeitbaren 
								Zahlenwert erzeugt.
							- 	str_shuffle() mischt die Zeichen eines übergebenen Strings zufällig durcheinander.
							- 	microtime() liefert einen Timestamp mit Millionstel Sekunden zurück (z.B. '0.57914300 163433596'),
								aus dem für eine URL-konforme Darstellung der Dezimaltrenner und das Leerzeichen entfernt werden.
						*/
						$fileName = mt_rand() . '_' . str_shuffle('abcdefghijklmnopqrstuvwxyz_-1234567890') . str_replace( array('.', ' '), '', microtime() );
						
						
						#********** GENERATE FILE EXTENSION **********#
						/*
							Aus Sicherheitsgründen wird nicht die ursprüngliche Dateinamenerweiterung aus dem
							Dateinamen verwendet, sondern eine vorgenerierte Dateiendung aus dem Array der 
							erlaubten MIME Types.
							Die Dateiendung wird anhand des ausgelesenen MIME Types [key] ausgewählt.
						*/
						$fileExtension = $imageAllowedMimeTypes[$imageMimeType];
						
						
						#********** GENERATE FILE TARGET **********#
						/*
							Endgültigen Speicherpfad auf dem Server generieren:
							destinationPath/fileName.fileExtension
						*/
						$fileTarget = $imageUploadPath . $fileName . $fileExtension;
						
if(DEBUG_F)			echo "<p class='debugImageUpload value'><b>Line " . __LINE__ . "</b>: \$fileTarget: $fileTarget <i>(" . basename(__FILE__) . ")</i></p>\n";
// if(DEBUG_F)			echo "<p class='debugImageUpload value'><b>Line " . __LINE__ . "</b>: Stringlänge: " . strlen($fileTarget) . " <i>(" . basename(__FILE__) . ")</i></p>\n";
						
						// PREPARE IMAGE FOR PERSISTANT STORAGE END
						#*******************************************************************#
						
						
						#*****************************************************#
						#********** MOVE IMAGE TO FINAL DESTINATION **********#
						#*****************************************************#						
						/*
							move_uploaded_file() verschiebt eine hochgeladene Datei an einen 
							neuen Speicherort und benennt die Datei um
						*/
						if( @move_uploaded_file($fileTemp, $fileTarget) === false ) {
							// 5. Fehlerfall: Bild konnte nicht verschoben/gespeichert werden
if(DEBUG_F)				echo "<p class='debugImageUpload err'><b>Line " . __LINE__ . "</b>: FEHLER beim Verschieben der Datei von '$fileTemp' nach '$fileTarget'! <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
							// Fehlermeldung für den User
							$errorMessage = 'Beim Speichern des Bildes ist ein Fehler aufgetreten. Bitte versuchen Sie es später noch einmal.';
							
							// Nicht existierenden Bildpfad löschen
							$fileTarget = NULL;
							
						} else {
							// Erfolgsfall
if(DEBUG_F)				echo "<p class='debugImageUpload ok'><b>Line " . __LINE__ . "</b>: Datei erfolgreich von '$fileTemp' nach '$fileTarget' verschoben. <i>(" . basename(__FILE__) . ")</i></p>\n";				
							
						} // MOVE IMAGE TO FINAL DESTINATION END
						#*******************************************************************#
	
					} // FINAL IMAGE VALIDATION END
					

					#********** RETURN ARRAY CONTAINING EITHER IMAGE PATH OR ERROR MESSAGE **********#
					return array( 'imagePath'=>$fileTarget, 'imageError'=>$errorMessage );
					
					#********** LOCAL SCOPE END **********#
				}


#**********************************************************************************#
?>