console.log("test dashboard");
// let footerNoticeWrap = document.querySelector(".stone-digital-support__plugin-wrap");
// let wpFooter = document.getElementById("wpfooter");
// console.log(wpFooter);
// let newNode = document.createElement("span");
// wpFooter.insertBefore(newNode, referenceNode)

jQuery( function() {
	
	let alertToggle = `<div class='sd-support__alert-toggle'>
		Alerts
		<span class='ab-icon dashicons dashicons-bell' aria-hidden='true'></span>
		<span class='sd-support__alert-toggle__number'>1</span>
	</div>`;

	let wpBodyContent = jQuery("#wpbody-content .wrap");
	let wpBodyNav = jQuery("#wpbody-content .wrap > h1");
	jQuery("<div class='stone-digital-support__plugin-wrap'></div>").insertAfter(wpBodyNav);
	jQuery(alertToggle).insertAfter(wpBodyNav);
	
	let footerNoticeWrap = jQuery(".stone-digital-support__plugin-wrap");
	let notices = document.querySelectorAll(".wrap > .notice, .wrap > .error, .wrap > .notice.notice-warning");
	console.log(notices);
	if( notices.length > 0) {
		for (var i = 0; i < notices.length; i++) {
			footerNoticeWrap.append(  notices[i] );
		}
		for (var i = 0; i < notices.length; i++) {
			notices[i].classList.add('in-footer');
		}
	} else {
		footerNoticeWrap.hide();
	
	}

	const nestedElements = footerNoticeWrap.children().length;
	console.log("Number of nested elements:", nestedElements)
	jQuery('.sd-support__alert-toggle').find(".sd-support__alert-toggle__number").text(nestedElements);

	jQuery(".sd-support__alert-toggle").click(function(){
		footerNoticeWrap.toggle();
		const toggleState = footerNoticeWrap.is(':visible');
		localStorage.setItem('stdToggle', toggleState);
	  });

	let customInlineStyle = "<style>#wpbody-content .wrap .notice { display: block; } </style>"
	jQuery("body").append(customInlineStyle);

	let getToggleStatus = localStorage.getItem('stdToggle');
	console.log(getToggleStatus);

	// Check the stored value
	if (getToggleStatus === 'true') {
		// Toggle the element to be visible
		footerNoticeWrap.show();
	} else {
		// Toggle the element to be hidden
		footerNoticeWrap.hide();
	}

	var $tabLinks = jQuery('.std-dashboard-tabs li.std-tab-btn a');
	var $activeSwitcher = jQuery('.std-dashboard-tabs .active-switcher');
  
	$tabLinks.on('click', function(e) {
	  e.preventDefault();
  
	  var $clickedTab = jQuery(this);
	  var tab = $clickedTab.attr('href');
  
	  // Update active tab in local storage
	  localStorage.setItem('lastActiveTab', tab);
  
	  // Remove active class from all tab links and tabs
	  $tabLinks.removeClass('active');
	  jQuery('.std-dashboard-tab').removeClass('active');
  
	  // Add active class to clicked tab link and corresponding tab content
	  $clickedTab.addClass('active');
	  jQuery(tab).addClass('active');
  
	  // Update active switcher position
	  var switcherLeft = $clickedTab.position().left;
	  var switcherWidth = $clickedTab.width() + 50;
	  $activeSwitcher.css({
		'left': switcherLeft,
		'width': switcherWidth
	  });
	});
  
	// Retrieve last active tab from local storage
	var lastActiveTab = localStorage.getItem('lastActiveTab');
	if (lastActiveTab) {
	  $tabLinks.filter('[href="' + lastActiveTab + '"]').click();
	} else {
	  $tabLinks.first().click(); // Default to the first tab
	}

	// Stone Digital support - Dashboard Tabs
	// jQuery( '.std-dashboard-tabs li.std-tab-btn a' ).on( 'click', function(e) {
	// 	console.log("Hello There");
	// 	e.preventDefault();
	// 	jQuery( '.std-dashboard-tabs li.std-tab-btn a' ).removeClass( 'active' );
	// 	jQuery(this).addClass( 'active' );
	// 	var tab = jQuery(this).attr( 'href' );
	// 	jQuery( '.std-dashboard-tab' ).removeClass( 'active' );
	// 	jQuery( '.std-wrap__settings-page' ).find( tab ).addClass( 'active' );
	// 	jQuery(".std-dashboard-tabs .active-switcher").css('width', jQuery(this).width()+50);
	// 	jQuery(".std-dashboard-tabs .active-switcher").css('left', jQuery(this).position().left);
	// });

});