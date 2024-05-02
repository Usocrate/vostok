class IndividualPhoto extends HTMLImageElement {
	constructor() {
		super();
		this.lowResSrc = this.src;
		this.hiResSrc = null;
		this.addEventListener('mouseover', this.getHiRes.bind(this));
		this.addEventListener('mouseout', this.getLowRes.bind(this));
	}
	
	async getHiRes(event) {
	    try {
			if (this.hiResSrc !== null) {
				this.src = this.hiResSrc; 
			} else {
				if (!trombiReworkUrl) {
					throw new Error('où chercher ?');
				}
				let urlToTest = trombiReworkUrl + this.dataset.individualId + '_hover.png';
				const response = await fetch(urlToTest);
				if (response.ok) {
					this.hiResSrc = urlToTest;
					this.src = this.hiResSrc;
				}
			}
	    } catch (error) {
	      console.error('Affichage de la photo hi-fi en échec :', error);
	      return false;
	    }
	}	
	
	getLowRes(event) {
		this.src = this.lowResSrc;
	}		
}