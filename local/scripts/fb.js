// JavaScript Document

//var isMobile = ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )  ? true : false;
var isMobile = (screen.width<=480) ? true : false;



$(document).ready(function() {
	
	  
	
	
	
	//click anywhere on shop item to go to product
	$(".shopItem").click(function() {		
		location.href = $(this).find("a").attr('href');
	});
	
	
	
	
	
	// JavaScript Document
	$(".collapsible").nextUntil(".collapsible").hide();
	$(".collapsible").click(function() { 
		$(this).nextUntil(".collapsible").slideToggle();
	});
	
	
	
	// concertina menus
	$("#articlesectionmenu ul ul").css("display","none");
	$("#articlesectionmenu ul li.selected ul").css("display","block");
	$("#articlesectionmenu > ul > li > a").click(function(){
		$("#articlesectionmenu ul ul").slideUp(); // optiona line closes existing lists
		if($(this).next().is("ul")) { 
			$(this).attr("href", "javascript:void(0)");
			$(this).next().slideToggle(300);
		}
	});
	
	$(".fancybox-hidden").hide(); // hides all fancybox content with this class added	
	$('.fancybox').fancybox({
		 beforeShow : function(){
			 // adds class to fancybox iframe - NOTE class  needs to be class="fancybox fancybox.iframe" for an iframe
 			$('.fancybox-iframe').contents().find('body').addClass("infancybox");
		}
	 });
	
	
	
	
	
	
	// div infinatescroll contains "pages" of contetn whch get added at end each time
    $(".infinatescroll table.pagination").hide(); // effectively hides pagination table
	var nextPage = 1;
	$(window).scroll(function(){
		if (nextPage>0) { // exists	
			if(isScrolledIntoView($(".infinatescroll"))) {
				url = "includes/events.inc.php?pageNum_rsEvents="+nextPage;				
				$.ajax({
					url: url, 
					success: function(result){	
						if(result) {			
							$(".infinatescroll").append(result);
							nextPage ++;
						} else {
							// no more data set page = 0
							nextPage = 0;
						}					
					}
				});				
			}
		}
	});
	
	/** LIVE SEARCH add to any text field that is in a form **/
	$(".livesearch").after("<div class='livesearchresults'></div>");	
	$(".livesearch").keyup(function() { 
		var theForm = $(this).parents("form");
		theForm.css("position","relative");
		if($(this).val().length>1) {			
			var postData = theForm.serialize();
			$.ajax({
				   type: "POST",
				   url: "/local/ajax/livesearch.ajax.php",
				   data: postData, 
				   timeout: 1000,
				   success: function(data)
				   {
					   theForm.find(".livesearchresults").html(data);
				   },
				   error: function(xhr, textStatus, errorThrown){
						//
					}
				 });	
		} else {
			theForm.find(".livesearchresults").html("");
		}	
	});	
	
	// make any links clickable within content with this class (only to be ised on plain text areas)
	$(".clickablelinks").each(function(){
		$(this).html($(this).html().replace("/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/i","<a href='$1'>$1</a>"));
	});
	
	
 });
 
 
 


	


function isScrolledIntoView(elem)
{
    var $elem = $(elem);
    var $window = $(window);

    var docViewTop = $window.scrollTop();
    var docViewBottom = docViewTop + $window.height();

    var elemTop = $elem.offset().top;
    var elemBottom = elemTop + $elem.height();

    //return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop)); // if all within screen
	return (elemBottom <= docViewBottom);
}


// Define: Linkify plugin
// example usage $("p").linkify(); $(".linkify").linkify();
(function($){

  var url1 = /(^|&lt;|\s)(www\..+?\..+?)(\s|&gt;|$)/g,
      url2 = /(^|&lt;|\s)(((https?|ftp):\/\/|mailto:).+?)(\s|&gt;|$)/g,

      linkifyThis = function () {
        var childNodes = this.childNodes,
            i = childNodes.length;
        while(i--)
        {
          var n = childNodes[i];
          if (n.nodeType == 3) {
           // var html = $.trim(n.nodeValue);
			 var html = n.nodeValue;
            if (html)
            {
              html = html.replace(/&/g, '&amp;')
                         .replace(/</g, '&lt;')
                         .replace(/>/g, '&gt;')
                         .replace(url1, '$1<a href="http://$2">$2</a>$3')
                         .replace(url2, '$1<a href="$2">$2</a>$5');
              $(n).after(html).remove();
            }
          }
          else if (n.nodeType == 1  &&  !/^(a|button|textarea)$/i.test(n.tagName)) {
            linkifyThis.call(n);
          }
        }
      };

  $.fn.linkify = function () {
    return this.each(linkifyThis);
  };

})(jQuery);


 
