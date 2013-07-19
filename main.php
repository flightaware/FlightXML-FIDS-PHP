
<html>
<head>
    <meta charset="utf-8" />
    <title>GA FIDS</title>
	
	<link href="css/fids.css" type="text/css" rel="stylesheet"/>
	
</head>
<body>
	<div id="tableHolder" class = "someClass"></div>
   
   
  <script src="jquery.js"></script>
    
    <script>
 
    $(document).ready(function(){
      refreshTable();
    });
    
    

    function refreshTable(){
    	var interval;
        $('#tableHolder').load('xml_parser.php', function(){
           var newinterval = $('#refresh').text();
           //alert("got newinterval " + newinterval);
           
           setTimeout(refreshTable, newinterval.valueOf()*1000);
        });
    }
 
 
	document.addEventListener("keydown", function(e) {
	  if (e.keyCode == 13) {
		toggleFullScreen();
	  }
	}, false);


	function toggleFullScreen() {
	  if (!document.fullscreenElement &&    // alternative standard method
		  !document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
		if (document.documentElement.requestFullscreen) {
		  document.documentElement.requestFullscreen();
		} else if (document.documentElement.mozRequestFullScreen) {
		  document.documentElement.mozRequestFullScreen();
		} else if (document.documentElement.webkitRequestFullscreen) {
		  document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		}
	  } else {
		if (document.cancelFullScreen) {
		  document.cancelFullScreen();
		} else if (document.mozCancelFullScreen) {
		  document.mozCancelFullScreen();
		} else if (document.webkitCancelFullScreen) {
		  document.webkitCancelFullScreen();
		}
	  }
	}

    </script>
    
 
</body>
</html>


