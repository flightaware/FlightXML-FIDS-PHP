FlightXML-FIDS-PHP
==================

This is an example Flight Information Display System (FIDS) using PHP and FlightXML, the FlightAware API.




About Fullscreen Display:

	1. The fullscreen mode is supported by Firefox 9.0, 10.0; Chrome 15, 20; Opera 12.10; Safai 5.0, 5.1; see

		https://developer.mozilla.org/en-US/docs/Web/Guide/DOM/Using_full_screen_mode
	
		for details.
	
	2. Press the Return or Enter key to enter fullscreen mode, press the ESC key (or F11) to exit fullscreen mode.
	
	3. Note that the fullscreen function is called on the html section that will call "xml_parser.php" file to generate tables. Table will automatically fill in
	   the full width of the screen, but the height of table depends on the number of flights being displayed and the height of rows.
	   User can customize the two parameters in "airport_config.php" and "fids.css" respectively according to the size of the screen.
	 


About Files:

	1. "airport_config" is the configuration file where you can change parameters.
	
	2. "css" file contains fids.css and background images used in css that users can customize. Refer to file "table_class.php"
		when doing the customization. Rows and headers in "table_class.php" have css classes that users can redefine, add or delete for their own designs.
		
	
	3. 





