class MembershipTitleAutocomplete extends HTMLInputElement {
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
      
        const response = await fetch(apiUrl+`memberships/titles.php?query=${encodeURIComponent(query)}`);
        if (!response.ok) {
          throw new Error('demande sans réponse');
        }
        const data = await response.json();
        return data.titles || [];
      } else {
        return [];
      }
    } catch (error) {
      console.error(error);
      return [];
    }
  }

  populateList(suggestions) {
    this.datalist.innerHTML = '';
    suggestions.forEach(suggestion => {
      const option = document.createElement('option');
      option.value = suggestion.value;
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
