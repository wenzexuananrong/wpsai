jQuery(document).ready(function(jQuery){
	var serch_by_order_number = wt_seq_settings.serch_by_order_number;
	if(serch_by_order_number === 'yes' && document.getElementById("order-search-filter")){
		const selectElement = document.getElementById("order-search-filter");
		const orderNumberOption = document.createElement("option");
		orderNumberOption.text = "Order Number";
		orderNumberOption.value = "order_number";
		if (window.location.search.includes("search-filter=order_number")) {
			orderNumberOption.selected = true; 
		  }
		const allOption = selectElement.querySelector("option[value='all']");
		selectElement.insertBefore(orderNumberOption,allOption);
	}
	
});