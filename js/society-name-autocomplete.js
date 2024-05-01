class SocietyNameAutocomplete extends HTMLInputElement {
  constructor() {
    super();
    this.datalist = null;
  }

  connectedCallback() {
    this.render();
    this.addEventListener('input', this.handleInput.bind(this));
  }

  async handleInput() {
    // Récupération de la valeur saisie par l'utilisateur
    const query = this.value;
    // Vérification de la longueur de la valeur saisie
    if (query.length > 2) {
      // Récupération des suggestions seulement si la longueur est supérieure à 2
      const suggestions = await this.fetchSuggestions(query);
      this.populateList(suggestions);
    } else {
      // Si la longueur est inférieure ou égale à 2, effacer les suggestions
      this.populateList([]);
    }
  }

  async fetchSuggestions(query) {
    try {
      // Vérification de la longueur de la valeur saisie
      if (query.length > 2) {
        // Appel de l'API pour récupérer les suggestions
        const response = await fetch(`api/societies/names.php?query=${encodeURIComponent(query)}`);
        // Vérification de la réponse du serveur
        if (!response.ok) {
          // Si la réponse n'est pas OK, lancer une erreur
          throw new Error('Échec de la récupération des suggestions');
        }
        // Conversion de la réponse en JSON
        const data = await response.json();
        // Retourner les suggestions récupérées (supposant que la clé est 'names' dans la réponse JSON)
        return data.names || [];
      } else {
        // Retourner un tableau vide si la longueur est inférieure ou égale à 2
        return [];
      }
    } catch (error) {
      // En cas d'erreur, afficher un message dans la console
      console.error('Erreur lors de la récupération des suggestions :', error);
      // Retourner un tableau vide en cas d'erreur
      return [];
    }
  }

  populateList(suggestions) {
    // Effacer les anciennes suggestions
    this.datalist.innerHTML = '';
    // Ajouter les nouvelles suggestions à l'élément datalist
    suggestions.forEach(suggestion => {
      const option = document.createElement('option');
      option.value = suggestion;
      this.datalist.appendChild(option);
    });
  }

  render() {
    // Création de l'élément datalist
    this.datalist = document.createElement('datalist');
    // Récupération de l'identifiant de l'élément input associé
    const inputId = this.getAttribute('id');
    // Définition de l'identifiant de l'élément datalist en incluant l'identifiant de l'élément input
    this.datalist.id = `${inputId}-list`;
    // Insertion de l'élément datalist dans le DOM
    this.parentNode.insertBefore(this.datalist, this.nextSibling);
    // Définition de l'attribut 'list' sur l'élément input pour le lier à l'élément datalist
    this.setAttribute('list', this.datalist.id);
  }
}

// Définition du custom element 'society-name-autocomplete'
customElements.define('society-name-autocomplete', SocietyNameAutocomplete, { extends: 'input' });
