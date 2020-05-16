var pageInitialized = false;


// redefine this if required customised basket functions
var ajax_buy_callback = ajax_buy_callback || function(result){
	
	  obj = JSON.parse(result);
	alert(obj.itemname+" x "+obj.quantity+" added to basket");
		
			$(".basket_total_items").html(parseInt($(".basket_total_items").html())+obj.quantity);
			if(document.location.href.indexOf("/basket") !== -1) {
				// if already on basket page refresh to include new item
				document.location.href="/products/basket/";
			}
}



$(document).ready(function(e) {
	// stop ready event firing more than once
	if(pageInitialized) return;
    pageInitialized = true;
	
	
	checkSavedBasketCookie();
	if(typeof(Storage)!=="undefined")
  {
 	$(".localStorage").show();
	if(!localStorage.favProducts) {
		localStorage.favProducts = "";
	}
	//alert(localStorage.favProducts);
	$(".favProducts .add").click(function() {
		
		var productID = $(this).attr("data-productID");
		addFavouriteProduct(productID);
		alert("Added to favourites");
		
	});
	
  }

 

  
  
  $(".ajax-buy .shopItem .addtobasket, .ajax-buy #productPage .addbasket").click(function(event) {
		event.preventDefault();
		data = $(this).closest("form").serialize();
    	$.post('/products/basket/index.php', data,  ajax_buy_callback);
	});
  
  
  $(".ajax-buy .freesample").click(function(event) {
	
		event.preventDefault();
		data = $(this).closest("form").serialize()+"&sample=true";
    	$.post('/products/basket/index.php', data,  ajax_buy_callback);
	});
 
 /* chafnge main image based on select option */
 
 $(".productoptions select").change(function() {
	 var optionID = $(this).val();
	 $("#productThumbs li.productoption"+optionID+" img").trigger( "click" );
 });
  
  
    
});


function addFavouriteProduct(ID) {
	if (localStorage.favProducts.length>0) {  
		favProducts = localStorage.favProducts.split(",");		
			if(favProducts.indexOf(ID.toString())>=0) {
				return false;	
			} else {
				localStorage.favProducts +=","+ID.toString();
				return true;
			}
	 } else {
	 	localStorage.favProducts = ID.toString(); 
		return true;
	 }	 
}

function removeFavouriteProduct(ID) {
	favProducts = localStorage.favProducts.split(",");
	var index = favProducts.indexOf(ID.toString());
	if (index > -1) {
    	favProducts.splice(index, 1);
	}		
	localStorage.favProducts = favProducts.join(); 
}

function showFavouriteProducts() {
	if (localStorage.favProducts.length>0) {
		window.location.href="/products/index.php?categoryID=-1&showproducts="+localStorage.favProducts;
	} else {
		alert("You have no favourite products saved.");
	}
}


function saveBasket() {
	if(confirm('Do you want to save your basket?\n\nWhen you return to the site you can return basket to its current state by clicking on the \'Restore saved basket\' link.\n\nNote: This feature is added for your convenience by storing a cookie on your computer.')) {
		setCookie("basket",document.getElementById('basketcontentsstring').value,31,"/");
		if(getCookie('basket')) { // success
		
		document.getElementById('saveBasket').style.display = 'none';
		document.getElementById('restoreBasket').style.display = 'block';
		} else {
			alert("In order to save your basket, you need \'cookies\' enabled on your browser.\n\nPlease see your browser help documentation on how to do this.");
		}
	}
}


function restoreBasket() {
	if(confirm('Are you sure you want to restore your saved basket?\n\nAny current items in your basket will be discarded.')) {
	window.location.href = "/products/basket/index.php?restoreBasket=true";
	}
}

function deleteBasket(force) {
	if(force = true || confirm('Are you sure you want to delete your saved basket?')) {
		deleteCookie("basket","/");
		document.getElementById('restoreBasket').style.display = 'none';
		document.getElementById('saveBasket').style.display = 'block';
	}
}

function checkSavedBasketCookie() {
	if(document.getElementById('saveBasket')) {
		savedbasket = getCookie("basket");
		if (savedbasket) { 
			document.getElementById('restoreBasket').style.display = 'block';
		} else {
			document.getElementById('saveBasket').style.display = 'block';
		}
	}
}



function addToBasket(theForm) {
	var errors = "";
	colourradio = false;
	colourvalue = "";
	optionradio = false;
	optionvalue = "";
	for(i=0; i<theForm.length; i++) {
		if(theForm[i].value == 'none-chosen') { 
			errors += "Please select an option from the "+theForm[i].title+" menu.\n" ;
		} 
		if(theForm[i].name.indexOf('quantity')==0 && theForm[i].value < 1)  {
			errors += "Please enter a quantity.\n" ;
		}
		if(theForm[i].name == 'finish' && theForm[i].type == 'radio') {
			colourradio = true;
			if(theForm[i].checked) {
				colourvalue = theForm[i].value;
			}
		}
		if(theForm[i].name == 'optionID' && theForm[i].type == 'radio') {
			optionradio = true;
			if(theForm[i].checked) {
				optionvalue = theForm[i].value;
			}
		}
	}
	
	
	if(colourradio==true && colourvalue=="") { 
		errors +="Please choose a colour/finish.\n";
	}
	
	if(optionradio==true && optionvalue=="") { 
		errors +="Please choose a product option.\n";
	}
	
	if(theForm.optiontextrequired.value == 1 && theForm.optiontext.value == "") {
		errors +="Please enter option text.\n";
	}
	
	if(theForm.optiontext2required && theForm.optiontext2required.value == 1 && theForm.optiontext2.value == "") {
		errors +="Please enter second option text.\n";
	}
	
	if(theForm.optiontext3required && theForm.optiontext3required.value == 1 && theForm.optiontext3.value == "") {
		errors +="Please enter third option text.\n";		
	}
	
	if(parseInt(theForm.quantity.value) > parseInt(theForm.stockquantity.value)) {
		errors += "Sorry, we only have "+parseInt(theForm.stockquantity.value)+" left in stock.\nPlease reduce your order to add to basket.\n";
	}
	
	if(errors !="") {
		alert(errors); return false;
	} else {
		return true;
	}
}