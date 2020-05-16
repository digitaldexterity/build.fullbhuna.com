// REQUIRES JQUERY
$(document).ready(function()
{
	$(".liveURLscrape").keyup(function()
	{
		if(!document.getElementById("liveScrapeOn") || document.getElementById("liveScrapeOn").checked) { // is on		
		var content=$(this).val();
		var urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
		// Filtering URL from the content using regular expressions
		var url= content.match(urlRegex);		
		if(url && url.length>0)
		{
			// get hostname from url
			var domain = getDomain(url);
			//alert(tmp.hostname);
			$(".liveURLscrapePreview").slideDown('show');
			$(".liveURLscrapePreview").html("<img src='/core/images/loading_16x16.gif'>");
			// Getting cross domain data 
			$.get("/core/ajax/urlscrape.ajax.php?url="+url,function(response)
			{
				// response contaons full HTML of page
				// Loading <title></title>data
				var titles=(/<title>(.*?)<\/title>/m).exec(response);
				if(titles) { // got doc OK
					var title=titles[1];
					$(".liveURLscrapeTitle").val(title);
					var imageHTML = "";
					var description = getDescription(response);
					if(description) $('.liveURLscrapeSummary').val(function(index, old) { return description+"\n\n" + old; });
									
					var imageURL = getFirstImage(response);				
					if(imageURL) {
						imageURL = (imageURL.indexOf("http")==0)	?  imageURL : "http://"+domain+imageURL;				
						imageHTML = "<img src='"+imageURL+"' class='img'/>";
						$(".liveURLscrapeImgPreview").html(imageHTML);
						$("input.liveURLscrapeImg").val(imageURL);
					}
					
					
					// build HTML
					var summaryHTML = imageHTML+"<h4>"+title+"</h4>";	
					if(description) {
						summaryHTML += "<p>"+description+"</p>";
					}
					summaryHTML +=" <p><a href='"+url+"'>"+url+"</a></p>";
					$(".liveURLscrapePreview").html(summaryHTML);
					
				}
				
				
			});		
		}
		}
		return false;
		
	});
});

function extractImageURLs(html) {
	var images=(/src=.((.*?)\.(png|jpg|gif))./mi).exec(html);
	
	return images;
	
}

function getFirstImage(html) {
	var ogimage = getMetaData(html, "og:image");
	if(ogimage) {
		return ogimage;
	} else {
		images = extractImageURLs(html);	
		if(images) {
			return images[1];
		}		
	}
	return false;
}

function getDescription(html) {
	// try OG tag, then meta description then forst para
	var ogdescription = getMetaData(html, "og:description");
	if(ogdescription) {
		return ogdescription;
	} else {
		var metadescription = getMetaData(html, "description");
		if(metadescription) {
			return metadescription;
		} else {
			var paragraphs=(/<p.*>(.*?)<\/p>/mi).exec(html);
			if(paragraphs) {
				return paragraphs[1];
			}
		}
	}
	return false;
}

function getDomain(url) {
	var tmp = document.createElement ('a');
	tmp.href = url;
	return tmp.hostname;
}

function getMetaData(html, name) {
	var r1 = /<meta.*[name|property]="/mi;
	var r2 = /".*content="(.*)"/mi;
	var regex = new RegExp(r1.source+name+r2.source);		
	var data = (regex).exec(html);
	if(data) {
		return data[1];
	} else {
		return false;
	}
}