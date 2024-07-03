    document.addEventListener('DOMContentLoaded', function () {
        const colorButtons = document.querySelectorAll('.color-button img');
        const currentUrl = window.location.href;
        const paramName = 'pb_color=';

        let endIndex = -1;
        let selectedColors = new Set();
        let paramIndex = currentUrl.indexOf(paramName);
        let paramValue = '';  
		if (paramIndex !== -1) {  
	        endIndex = currentUrl.indexOf('&', paramIndex) !== -1 ? currentUrl.indexOf('&', paramIndex) :  
	             currentUrl.indexOf('#', paramIndex) !== -1 ? currentUrl.indexOf('#', paramIndex) :  
	             currentUrl.length;  

			paramValue = currentUrl.substring(paramIndex + paramName.length, endIndex);  
			let colorArray = paramValue.split(',');
			selectedColors = new Set(colorArray);
		} 

        colorButtons.forEach(button => {
        	const color = button.getAttribute('data-color');
        	if (!selectedColors.has(color)) {
                selectedColors.delete(color);
                button.classList.remove('selected');
            } else {
                selectedColors.add(color);
                button.classList.add('selected');
            }

            button.addEventListener('click', () => {
                if (paramIndex !== -1) {
	                if (!selectedColors.has(color)) {  
					    button.classList.add('selected');  
					    selectedColors.add(color);  
					} else {
						button.classList.remove('selected');
						selectedColors.delete(color);
					}

					paramValue = Array.from(selectedColors).join(',');
					newUrl = currentUrl.substring(0, paramIndex) +   
			           paramName +   
			           paramValue +   
			           (endIndex < currentUrl.length ? currentUrl.substring(endIndex) : '');
			    } else {
			    	let sperator = currentUrl.indexOf('?', paramIndex) === -1 ? '?' : '&';
			    	newUrl = currentUrl + sperator + `${paramName}${color}`;
			    }

                window.location.href = newUrl;
            });
        });
    });
