class IndividualPhoto extends HTMLImageElement {
	constructor() {
		super();
		this.reworkedSrc = this.src;
		this.reworkedHOverSrc = null;
		this.addEventListener('mouseover', this._setHOverSrc.bind(this));
		this.addEventListener('mouseout', this._resetReworkedSrc.bind(this));
	}
	_setHOverSrc(event) {
		if (this.reworkedHOverSrc!==null) {
			this.src = this.reworkedHOverSrc; 
		} else {
			let foundImgSrc=null;
			let urlToTest = trombiReworkUrl+this.dataset.individualId+'_hover.png';
			let xhr = new XMLHttpRequest();

			xhr.onreadystatechange = function () {
				if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
					foundImgSrc=urlToTest;
				}				  
			};

			xhr.open("GET", urlToTest, false);
			xhr.send();
			
			if (foundImgSrc!==null) {
				this.reworkedHOverSrc = foundImgSrc;
				this.src = this.reworkedHOverSrc;
			}
		}
	}	
	
	_resetReworkedSrc(event) {
		this.src=this.reworkedSrc;
	}		
}