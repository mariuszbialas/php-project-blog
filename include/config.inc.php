<?php
#**********************************************************************************#

				
				#******************************************#
				#********** GLOBAL CONFIGURATION **********#
				#******************************************#
				
				/*
					Konstanten werden in PHP mittels der Funktion define() oder über 
					das Schlüsselwort const (const DEBUG = true;) definiert. Seit PHP7
					ist der Unterschied zwischen den beiden Varianten, dass über 
					const definierte Konstanten nicht innerhalb von Funktionen, Schleifen, 
					if-Statements oder try/catch-Blöcken definiert werden können. 
					Konstanten besitzen im Gegensatz zu Variablen kein $-Präfix
					Üblicherweise werden Konstanten komplett GROSS geschrieben.
					
					Konstanten können in PHP auf zwei unterschiedliche Arten deklariert werden:
					Über das Schlüsselwort const und über die Funktion define().
					
					const DEBUG = true;
					define('DEBUG', true);
				*/
				
				
				#********** DATABASE CONFIGURATION **********#
				define('DB_SYSTEM',						'mysql');
				define('DB_HOST',						'localhost');
				define('DB_NAME',						'blogprojekt');
				define('DB_USER',						'root');
				define('DB_PWD',						'');
				
				
				#********** EXTERNAL STRING INPUT CONFIGURATION **********#
				define('INPUT_MIN_LENGTH',				0);
				define('INPUT_MAX_LENGTH',				256);
				
				
				#********** IMAGE UPLOAD CONFIGURATION **********#
				define('IMAGE_MAX_WIDTH',				800);
				define('IMAGE_MAX_HEIGHT',				800);
				define('IMAGE_MIN_SIZE',				1024);
				define('IMAGE_MAX_SIZE',				128*1024);
				define('IMAGE_ALLOWED_MIME_TYPES',	array('image/jpeg'=>'.jpg', 'image/jpg'=>'.jpg', 'image/gif'=>'.gif', 'image/png'=>'.png'));
				
				
				#********** STANDARD PATHS CONFIGURATION **********#
				define('IMAGE_UPLOAD_PATH',	'./uploaded_images/'); // Ort für uploaded Dateien
				
				
				#********** DEBUGGING **********#
				define('DEBUG', 						true);		// Debugging for main document
				define('DEBUG_V', 						true);		// Debugging for values	
				define('DEBUG_F', 						true);		// Debugging for functions	
				define('DEBUG_DB', 						true);		// Debugging for DB operations	
								

#**********************************************************************************#
?>