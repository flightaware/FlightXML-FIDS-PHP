FlightXML-FIDS-PHP
==================

This is an example Flight Information Display System (FIDS) using PHP and FlightXML, the FlightAware API.




About Fullscreen Display:
-------------------------

1.	The fullscreen mode is supported by Firefox 9.0, 10.0; Chrome 15, 20; Opera 12.10; Safai 5.0, 5.1; check [this] (https://developer.mozilla.org/en-US/docs/Web/Guide/DOM/Using_full_screen_mode)for details.

2.	Press the Return or Enter key to enter fullscreen mode, press the ESC key (or F11) to exit fullscreen mode.
	
3.	Note that the fullscreen function is called on the html section that will call "xml_parser.php" file to generate tables. Table will automatically fill in
	   the full width of the screen, but the height of table depends on the number of flights being displayed. You can change the height of each table row and font size
	   by adjusting "css/fids.css". The unit is "vw", one percent of width of the screen or "vh", one percent of the height of the screen. So that no matter what are
	   the real dimensions of the actual displaying screen, the ratio that table, column, row and fonts to the screen is fixed.


About Files:
------------
1.	"airport_config" is the configuration file where you can change parameters. There're detailed comments about setting each parameter in "airport_config.php".
	
	
2.	"css" file contains fids.css and background images used in css that users can customize. Refer to file "table_class.php"
	when doing the customization. Rows and headers in "table_class.php" have css classes that users can redefine, add or delete for their own designs.
		
	
3.	"main.php" is the file you run in the browser that will generate the fids in an environment with php installed. 
	
4.	"table_class.php" is the file contains a table_class that will send xml request to our server, get the flights information and generate tables that display the information.
	
5.	"xml_parser.php" is simply the file that call the table_class in "table_class.php".
	
	





