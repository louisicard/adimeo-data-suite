(function() {

  var ADS = function() {

    this.adsUrl = null;
    this.spid = null;
    this.blocks = [];
    this.query = '';
    this.filters = [];
    this.from = 0;
    this.spDefinition = null;
    this.searchResults = null;

    this.init = function() {
      var script = document.getElementById('ads--script');
      var path = script.attributes['src'].value;
      this.adsUrl = path.split('//')[0] + '//' + path.split('//')[1].split('/')[0];
      this.spid = path.split('?')[1].split('=')[1];

      var css = document.createElement('LINK');
      css.setAttribute('rel', 'stylesheet');
      css.setAttribute('href', this.adsUrl + '/css/ads-client.css');
      document.getElementsByTagName('HEAD')[0].appendChild(css);

      this.blocks = this.findElementsByAttribute('ads-block', '*');
      this.renderBlocks();
    }

    this.renderBlocks = function() {
      for(var i = 0; i < this.blocks.length; i++) {
        this.renderBlock(this.blocks[i]);
      }
    }

    this.renderBlock = function(block) {
      var blockType = block.attributes['ads-block'].value;
      if(blockType == 'search') {
        this.renderSearchBlock(block);
      }
      else if(blockType == 'facets') {
        this.renderFacetsBlock(block);
      }
      else if(blockType == 'search-results') {
        this.renderSearchResults(block);
      }
      else if(blockType == 'pager') {
        this.renderPager(block);
      }
    }

    this.renderSearchBlock = function(block) {

      this.clearBlock(block);

      var form = document.createElement('FORM');

      var input = document.createElement('INPUT');
      input.setAttribute('type', 'text');
      input.setAttribute('class', 'ads--search-input');
      input.setAttribute('placeholder', 'Search by keywords...');
      input.setAttribute('value', this.query);
      form.appendChild(input);

      var submitBtn = document.createElement('INPUT');
      submitBtn.setAttribute('type', 'submit');
      submitBtn.setAttribute('value', 'Search');
      form.appendChild(submitBtn);

      form.addEventListener('submit', function(event) {
        event.preventDefault();
        var form = event.target;
        var query = __adsClient.findElementsByAttribute('class', 'ads--search-input', form)[0].value;
        if(query.length > 0) {
          __adsClient.query = query;
          __adsClient.resetSerchParameters();
          __adsClient.fireSearch();
        }
        else {
          alert('You must provide a search query');
        }
        return false;
      });

      block.appendChild(form);
    }

    this.renderFacetsBlock = function(block) {
      this.clearBlock(block);

      if(this.searchResults != null && typeof this.searchResults.aggregations !== 'undefined') {
        var facetNames = this.getFacetNamesFromDef();
        for(var i in facetNames) {
          if(this.searchResults.aggregations[facetNames[i]].buckets.length > 0) {
            block.appendChild(this.renderFacetBloc(facetNames[i]));
          }
        }
      }
    }

    this.renderFacetBloc = function(facetName) {
      var facet = this.searchResults.aggregations[facetName];

      var node = document.createElement('DIV');
      node.setAttribute('class', 'ads--facet ads--facet--' + facetName.replace(/\./i, '_'));
      node.setAttribute('ads-facet', facetName);

      var title = document.createElement('DIV');
      title.setAttribute('class', 'ads--facet-title');
      var facetDef = null;
      for(var i = 0; i < this.spDefinition.facets.length; i++) {
        for(var kkk in this.spDefinition.facets[i]) {
          if(kkk == facetName) {
            facetDef = this.spDefinition.facets[i][kkk];
          }
        }
      }
      title.textContent = facetDef.label;
      node.appendChild(title);

      var ul = document.createElement('UL');
      node.appendChild(ul);

      for(var i = 0; i < facet.buckets.length; i++) {
        var li = document.createElement('LI');

        var link = document.createElement('A');
        link.setAttribute('href', '#');
        link.setAttribute('ads-facet-value', facet.buckets[i].key);
        link.addEventListener('click', function(event) {
          event.preventDefault();
          var link = event.target;

          var facetName = link.parentNode.parentNode.parentNode.attributes['ads-facet'].value;
          var value = link.attributes['ads-facet-value'].value;
          var filterValue = facetName + '="' + value.replace(/"/g, '\\"') + '"';
          if(__adsClient.filters.indexOf(filterValue) < 0) {
            __adsClient.filters.push(filterValue);
            __adsClient.from = 0;
            __adsClient.fireSearch();
          }
          return false;
        });
        link.textContent = facet.buckets[i].key + ' (' + facet.buckets[i].doc_count + ')';
        li.appendChild(link);
        ul.appendChild(li);
      }

      return node;
    }

    this.renderPager = function(block) {
      this.clearBlock(block);

      var ul = document.createElement('UL');
      ul.setAttribute('class', 'ads--pager');
      var nbPages = 0;
      if(this.searchResults != null && typeof this.searchResults.hits !== 'undefined' && typeof this.searchResults.hits.hits !== 'undefined' && this.searchResults.hits.hits.length > 0) {
        var currentPage = this.from / this.spDefinition.size + 1;
        var previousPage = currentPage > 1 ? currentPage - 1 : null;
        var nextPage = (currentPage + 1) <= Math.ceil(this.searchResults.hits.total / this.spDefinition.size) ? currentPage + 1 : null;

        if(previousPage != null) {
          var li = this.renderPagerPage(previousPage, 'Prev');
          li.setAttribute('class', 'prev');
          ul.appendChild(li);
        }
        var pages = [];
        var i = currentPage;
        while(nbPages <= 3 && i > 0) {
          var li =  this.renderPagerPage(i, i);
          pages = [li].concat(pages);
          nbPages++;
          i--;
        }
        i = currentPage + 1;
        while(nbPages < 6 && i <= Math.ceil(this.searchResults.hits.total / this.spDefinition.size)) {
          var li =  this.renderPagerPage(i, i);
          pages.push(li);
          nbPages++;
          i++;
        }
        for(var page in pages) {
          ul.appendChild(pages[page]);
        }
        if(nextPage != null) {
          var li =  this.renderPagerPage(nextPage, 'Next');
          li.setAttribute('class', 'next');
          ul.appendChild(li);
        }
      }
      if(nbPages > 1)
        block.appendChild(ul);
    }

    this.renderPagerPage = function(page, text) {
      var li = document.createElement('LI');
      if(page == this.from / this.spDefinition.size + 1)
        li.setAttribute('class', 'active');
      var link = document.createElement('A');
      link.setAttribute('href', '#');
      link.setAttribute('ads-pager-page', page);
      link.addEventListener('click', function(event) {
        event.preventDefault();
        var link = event.target;
        var page = link.attributes['ads-pager-page'].value;
        __adsClient.from = (page - 1) * __adsClient.spDefinition.size;
        __adsClient.fireSearch();
        return false;
      });
      link.textContent = text;
      li.appendChild(link);
      return li;
    }

    this.renderSearchResults = function(block) {
      this.clearBlock(block);

      if(this.searchResults != null && typeof this.searchResults.hits !== 'undefined' && typeof this.searchResults.hits.hits !== 'undefined' && this.searchResults.hits.hits.length > 0) {
        var wrapper = document.createElement('DIV');
        wrapper.setAttribute('class', 'ads--search-results');
        block.appendChild(wrapper);

        var sumary = document.createElement('DIV');
        sumary.setAttribute('class', 'ads--search-results-sumary');
        sumary.textContent = this.searchResults.hits.total + ' results for your search';
        wrapper.appendChild(sumary);

        var ul = document.createElement('UL');
        wrapper.appendChild(ul);

        for(var i = 0; i < this.searchResults.hits.hits.length; i++) {
          var hit = this.searchResults.hits.hits[i];
          var li = document.createElement('LI');
          li.setAttribute('class', 'ads--search-results-result-item');
          ul.appendChild(li);

          if(this.spDefinition.results.title.length > 0 && typeof hit._source[this.spDefinition.results.title] !== 'undefined') {
            var title = document.createElement('DIV');
            title.setAttribute('class', 'ads--search-results-result-item--title');
            title.textContent = hit._source[this.spDefinition.results.title];
            li.appendChild(title);
          }
        }
      }
    }

    this.getFacetNamesFromDef = function() {
      var names = [];
      for(var i = 0; i < this.spDefinition.facets.length; i++) {
        for(var kkk in this.spDefinition.facets[i]) {
          names.push(kkk);
        }
      }
      return names;
    }

    this.clearBlock = function(block) {
      for(var i = block.childNodes.length - 1; i >= 0; i--) {
        block.removeChild(block.childNodes[i]);
      }
    }

    this.resetSerchParameters = function() {
      this.filters = [];
    }

    this.fireSearch = function() {
      if(this.spDefinition == null) {
        this.sendRequest(this.adsUrl + '/api/sp-definition/' + this.spid, function (text) {
          var json = JSON.parse(text);
          __adsClient.spDefinition = json;
          __adsClient.executeSearch();
        });
      }
      else {
        this.executeSearch();
      }
    }

    this.executeSearch = function() {
      var facetNames = this.getFacetNamesFromDef();
      var url = this.adsUrl + '/search-api/v2?mapping=' + this.spDefinition.mapping + '&analyzer=' + this.spDefinition.analyzer + '&query=' + encodeURIComponent(this.query) + '&facets=' + encodeURIComponent(facetNames.join(','));
      url += '&size=' + this.spDefinition.size;
      url += '&from=' + this.from;
      for(var i in this.filters) {
        url += '&filter[]=' + encodeURIComponent(this.filters[i]);
      }
      var blocks = this.findElementsByAttribute('ads-block', '*');
      for(var i in blocks) {
        blocks[i].textContent = 'Loading. Please wait...';
      }
      this.sendRequest(url, function(data) {
        data = JSON.parse(data);
        __adsClient.searchResults = data;
        __adsClient.renderBlocks();
      });
    }

    this.sendRequest = function(url, callback) {
      var xmlhttp;
      // compatible with IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
      xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
          callback(xmlhttp.responseText);
        }
      }
      xmlhttp.open("GET", url, true);
      xmlhttp.send();
    }

    this.findElementsByAttribute = function(attr, val, root) {
      var res = [];
      var searcher = function(root, ads) {
        for(var i = 0; i < root.childNodes.length; i++) {
          var attrs = root.childNodes[i].attributes;
          if(typeof attrs !== 'undefined') {
            for (var j = 0; j < attrs.length; j++) {
              if(attrs[j].name == attr && attrs[j].value == val || attrs[j].name == attr && val == '*') {
                res.push(root.childNodes[i]);
              }
            }
          }
          searcher(root.childNodes[i], ads);
        }
      };
      searcher(typeof root ==='undefined' ? document.body : root, this);
      return res;
    }

  }

  var __adsClient = null;

  document.addEventListener('DOMContentLoaded', function() {
    __adsClient = new ADS();
    __adsClient.init();
  });

})();