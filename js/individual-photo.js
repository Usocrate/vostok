class IndividualPhoto extends HTMLImageElement {
	constructor() {
		super();
		this.reworkedSrc = this.src;
		this.originalSrc = null;
		this.addEventListener('mouseover', this._setOriginalSrc.bind(this));
		this.addEventListener('mouseout', this._resetReworkedSrc.bind(this));
	}
	
	_setOriginalSrc(event) {
		if (this.originalSrc!==null) {
			this.src = this.originalSrc; 
		} else {
			var foundImgSrc=null;
			for (let e of imageFileExtensions) {
				if (foundImgSrc!==null) {
					this.originalSrc = foundImgSrc;
					this.src = this.originalSrc;
					break;
				}
								
				let urlToTest = trombiUrl+this.dataset.individualId+'.'+e;
				//console.log('test '+urlToTest);

				let xhr = new XMLHttpRequest();

				xhr.onreadystatechange = function () {
					if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
						console.log('something found at '+urlToTest+'!');
						foundImgSrc=urlToTest;
					}				  
				};

				xhr.open("GET", urlToTest, false);
				xhr.send();
			}
		}
	}
	
	_resetReworkedSrc(event) {
		this.src=this.reworkedSrc;
	}		
}