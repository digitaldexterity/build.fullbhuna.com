var blogentryID = 0;
var createdbyID = 0;

$(document).ready(function(e) {
	if(typeof(eventgroupID) !='undefined') {
		eventgroupID = $("#ID").val();
	} else {
		eventgroupID = 0;
		blogentryID = $("#ID").val();
	}
	createdbyID = $("#createdbyID").val();
	showTags();
	
	$(".add_tag").click(function() { 
		var tagID = $("#tagselect").val();
		if(tagID>=0) {
			var query = "";
			if(tagID==0) { // new entry
				query = "&tagname="+escape($("#tagname").val());
			} 
			
			$.ajax({url: "/core/tags/ajax/addtag.ajax.php?tagID="+tagID+"&blogentryID="+blogentryID+"&eventgroupID="+eventgroupID+"&createdbyID="+createdbyID+query, success: function(result){
				//$("#info").html(result);
				showTags();
				}
			});
		}		
	}); 
	
	
	
	$(document).on("click", ".delete_tag", function(){
		$.ajax({url: "/core/tags/ajax/removetag.ajax.php?taggedID="+$(this).attr("data-taggedID"), success: function(result){
        	showTags();
			}
		});
	});
	
	$("#tagselect").change(function() { 
		if($(this).val()==0 ){
			$("#tagname").show();
		} else {
			$("#tagname").hide();
		}
	});
});

function showTags() {
	$.ajax({url: "/core/tags/ajax/tags.ajax.php?blogentryID="+blogentryID+"&eventgroupID="+eventgroupID, success: function(result){
        	$("#tags").html(result);
		}
    });
}