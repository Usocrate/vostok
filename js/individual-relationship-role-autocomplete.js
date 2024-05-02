class IndividualRelationshipRoleAutocomplete extends HTMLInputElement {
  constructor() {
    super();
    this.datalist = null;
  }

  connectedCallback() {
    this.render();
    this.addEventListener('input', this.handleInput.bind(this));
  }

  async handleInput() {
    const query = this.value;
    if (query.length > 2) {
      const suggestions = await this.fetchSuggestions(query);
      this.populateList(suggestions);
    } else {
      this.populateList([]);
    }
  }

  async fetchSuggestions(query) {
    try {
      if (query.length > 2) {
        if (!apiUrl) {
          throw new Error('où chercher ?');
        }
      
        const response = await fetch(apiUrl+`relationships/roles.php?searchPattern=${encodeURIComponent(query)}&rolePlayerClass=individual`);
        if (!response.ok) {
          throw new Error('demande sans réponse');
        }
        const data = await response.json();
        return data.roles || [];
      } else {
        return [];
      }
    } catch (error) {
      console.error('Suggestion en échec :', error);
      return [];
    }
  }

  populateList(suggestions) {
    this.datalist.innerHTML = '';
    suggestions.forEach(suggestion => {
      const option = document.createElement('option');
      option.value = suggestion.role;
      this.datalist.appendChild(option);
    });
  }

  render() {
    this.datalist = document.createElement('datalist');
    const inputId = this.getAttribute('id');
    this.datalist.id = `${inputId}-list`;
    this.parentNode.insertBefore(this.datalist, this.nextSibling);
    this.setAttribute('list', this.datalist.id);
  }
}
