# Adimeo Data Suite: Documentation Search API

## Requirement
In order to run the examples in this documentation, please install the API_DEMO database as follows.<br />
[ Skip the step ](#summary)

### Data installation
#### Create index
* In `DataStudio > Indexes`, click on `Add a new index`:
    * Fill the `Index name` field with the value `api_demo`
    * Fill the `Settings` field as follow: 
        ```json
            {
                "analysis": {
                    "filter": {
                        "french_stop": {
                            "type": "stop",
                            "stopwords": "_french_"
                        },
                        "french_elision": {
                            "type": "elision",
                            "articles": [
                                "l",
                                "m",
                                "t",
                                "qu",
                                "n",
                                "s",
                                "j",
                                "d",
                                "c",
                                "jusqu",
                                "quoiqu",
                                "lorsqu",
                                "puisqu"
                            ]
                        },
                        "french_stemmer": {
                            "name": "light_french",
                            "type": "stemmer"
                        }
                    },
                    "analyzer": {
                        "french": {
                            "filter": [
                                "standard",
                                "asciifolding",
                                "french_elision",
                                "lowercase",
                                "french_stop",
                                "french_stemmer"
                            ],
                            "tokenizer": "standard"
                        },
                        "transliterator": {
                            "filter": [
                                "standard",
                                "asciifolding",
                                "lowercase"
                            ],
                            "tokenizer": "keyword"
                        }
                    }
                },
                "number_of_replicas": "1",
                "number_of_shards": "5"
            }
         ```
    * Save the configuration by clicking on `Create index` button
        
#### Create mapping
* In `DataStudio > Indexes`, click on `Add a mapping` on the line concerned by the index `api_demo`:
    * Fill the `Mapping name` field with the value `data_demo`
    * Click on `Show/hide JSON definition` and fill field as follow: 
        ```json
            {
                "body": {
                    "type": "text",
                    "boost": 5,
                    "store": true,
                    "analyzer": "standard"
                },
                "city": {
                    "type": "text",
                    "store": true,
                    "fields": {
                        "raw": {
                            "type": "keyword",
                            "store": true
                        },
                        "transliterated": {
                            "type": "text",
                            "store": true,
                            "analyzer": "transliterator"
                        }
                    },
                    "analyzer": "standard"
                },
                "color": {
                    "type": "text",
                    "store": true,
                    "fields": {
                        "raw": {
                            "type": "keyword",
                            "store": true
                        }
                    },
                    "analyzer": "standard"
                },
                "entity": {
                    "type": "text",
                    "store": true,
                    "fields": {
                        "raw": {
                            "type": "keyword",
                            "store": true
                        },
                        "transliterated": {
                            "type": "text",
                            "store": true,
                            "analyzer": "transliterator"
                        }
                    },
                    "analyzer": "standard"
                },
                "thumbnail": {
                    "type": "text",
                    "store": true
                },
                "url": {
                    "type": "text",
                    "store": true
                }
            }
        ```     
        * Save the configuration by clicking on `Save mapping` button

#### Create processor
* In `DataStudio > Processors`, click on `import`:
    * Select `documentation\processor_api_demo.data_demo.json` file
    * Import the configuration by clicking on `Import` button
    
#### Import data in data source
* In `DataStudio > Datasources`, select the `API_Examples` row and click on `Execute` action:
    * Fill the `File path` field with the local path of the `documentation\Api_Examples.csv`    
    * Click on `Execute` button to proceed import    

<a name="summary"></a>
## Summary
1. [ Search : Main call ](#search-main)
2. [ Search : Optional additional parameters ](#search-optional-additional-parameters)
    1. [ Searching for a string into indexed fields ](#search-query)  
    2. [ View a single document ](#search-single-document)  
    3. [ View a document list ](#search-document-list)         
    4. [ Filter on raw or transliterated field ](#search-filter)     
    5. [ Query string on analyzed field ](#search-query-string)  
    6. [ Display auto-promote ](#search-auto-promote)     
    7. [ Sort filter ](#search-sort-filter)      
    8. [ Suggestion fields ](#search-suggestion-field)  
    9. [ Highlights ](#search-highlights)         
    10. [ Facets ](#search-facets)
    11. [ Index analyzer ](#search-analyzer)   
    12. [ Boost query ](#search-boost-query)    
3. [ Autocomplete ](#autocomplete)
4. [ See more like this ](#more-like-this)
5. [ Custom search ](#custom-search)
----
<a name="search-main"></a>
### Search : Main call
Main call return all data for an index and a mapping.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.

* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`

    * **Call:**
      ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo
      ```   

    * **Code:** 200
    
    * **Content:**<br />
      ```json  
        {   
            "took": 4,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 3,
                "max_score": 5,
                "hits": [
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHI0E5a6YCxlRadcQ",
                        "_score": 5,
                        "_source": {
                            "body": "Nous sommes  l\u054ecoute de vos problmatiques pour dterminer laccompagnement le plus pertinent en fonction de votre contexte, de vos dlais et de votre budget. Contactez-nous pour connatre nos tarifs et disponibilits.",
                            "city": "Metz",
                            "entity": "Opcoding",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=1079",
                            "url": "https:\/\/www.opcoding.eu",
                            "color": "bleu"
                        }
                    },
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJQs5a6YCxlRadcd",
                        "_score": 5,
                        "_source": {
                            "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                            "city": "Paris",
                            "entity": "Adimeo",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                            "url": "https:\/\/adimeo.com",
                            "color": "rouge"
                        }
                    },
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJV45a6YCxlRadce",
                        "_score": 5,
                        "_source": {
                            "body": "Notre agence inbound marketing met en place des dispositifs de contenus performants pour aider PME, ETI et Grands groupes  gnrer des leads de faon plus directe et moins coteuse.",
                            "city": "Paris",
                            "entity": "Comexplorer",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=134",
                            "url": "https:\/\/www.comexplorer.com",
                            "color": "vert"
                        }
                    }
                ]
            }
        }
      ```
          
* **Error Response:**

    * **Code:** 400
    
    * **Content:** <br />
      Occurs when the elasticsearch query fails :
      ```json  
        {    
            "error": "Search failed"
        }
      ```

    * **Code:** 400
    
    * **Content:** <br />
      Occurs when index or mapping not exists :
      ```json  
        {    
            "error": "Mapping does not exists"
        }
      ```
  
    * **Code:** 400
    
    * **Content:** <br />
      Occurs when index or mapping parameters is missing :
      ```json  
        {    
            "error": "Missing mapping parameter"
        }
      ```      

<a name="search-optional-additional-parameters"></a>
### Search : Optional additional parameters
Optional additional parameters can be combined with each other based on the main call.

<a name="search-query"></a>
#### Searching for a string into indexed fields   
Return all the data corresponding to the exact search of the characters. 
The partial search can be done thanks to the joker (*).

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&query=`[your_character_search]

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.  
  * `query=[string]`: Characters to search in result.

* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [your_character_search] = `digit*`
    
    * **Call:**    
      ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&query\=digit\*
      ```    

    * **Code:** 200
    
    * **Content:** <br />    
      ```json    
         {
            "took": 17,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 1,
                "max_score": 5,
                "hits": [
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJQs5a6YCxlRadcd",
                        "_score": 5,
                        "_source": {
                            "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                            "city": "Paris",
                            "entity": "Adimeo",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                            "url": "https:\/\/adimeo.com",
                            "color": "rouge"
                        }
                    }
                ]
            }
         }
      ```  

<a name="search-single-document"></a>
#### View a single document
Return all data for a single document with its ID.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&doc_id=`[you_document_id]

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.  
  * `doc_id=[string]`: ID of the document to display.

* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [you_document_id] = `AWhxHJQs5a6YCxlRadcd` [Adjust this value for your documents]
    
    * **Call:**    
      ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&doc_id\=AWhxHJQs5a6YCxlRadcd
      ```    

    * **Code:** 200
    
    * **Content:** <br />
      ```json
        {
            "took": 6,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 1,
                "max_score": 1,
                "hits": [
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJQs5a6YCxlRadcd",
                        "_score": 1,
                        "_source": {
                            "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                            "city": "Paris",
                            "entity": "Adimeo",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                            "url": "https:\/\/adimeo.com",
                            "color": "rouge"
                        }
                    }
                ]
            }
        }
      ```  

<a name="view-document-list"></a>
#### Search: View a document list
Return all data for a document list specified by their ID.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&ids=`[your_document_id1],[your_document_id2]

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.  
  * `ids=[string]`: ID of the document to display separate by `,`.

* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [you_document_id1] = `AWhxHJV45a6YCxlRadce` [Adjust this value for your documents]
    * [you_document_id2] = `AWhxHJQs5a6YCxlRadcd` [Adjust this value for your documents]

    * **Call:**    
      ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&ids\=AWhxHJV45a6YCxlRadce,AWhxHJQs5a6YCxlRadcd
      ```

    * **Code:** 200
  
    * **Content:** <br /> 
      ```json
          {
                "took": 3,
                "timed_out": false,
                "_shards": {
                    "total": 5,
                    "successful": 5,
                    "skipped": 0,
                    "failed": 0
                },
                "hits": {
                    "total": 2,
                    "max_score": 6,
                    "hits": [
                        {
                            "_index": "api_demo",
                            "_type": "data_demo",
                            "_id": "AWhxHJQs5a6YCxlRadcd",
                            "_score": 6,
                            "_source": {
                                "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                                "city": "Paris",
                                "entity": "Adimeo",
                                "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                                "url": "https:\/\/adimeo.com",
                                "color": "rouge"
                            }
                        },
                        {
                            "_index": "api_demo",
                            "_type": "data_demo",
                            "_id": "AWhxHJV45a6YCxlRadce",
                            "_score": 6,
                            "_source": {
                                "body": "Notre agence inbound marketing met en place des dispositifs de contenus performants pour aider PME, ETI et Grands groupes  gnrer des leads de faon plus directe et moins coteuse.",
                                "city": "Paris",
                                "entity": "Comexplorer",
                                "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=134",
                                "url": "https:\/\/www.comexplorer.com",
                                "color": "vert"
                            }
                        }
                    ]
                }
          }
      ```

<a name="search-filter"></a>
#### Search: Filter on raw or transliterated field
Return all the data constrained by search filter on raw or transliterated field.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&filter[]=`[field_name_1][operator_1][value_1]`&filter[]=`[field_name_2][operator_2][value_2]...

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `filter[]=[array]`: Defines which search filter.<br /> 
  Each filter must respect the following pattern: `[field name][operator][value]`.<br /> 
  The list of available **operators** is:
    * `=`
    * `!=`
    * `>=`
    * `>`
    * `<=`
    * `<=>` 
   
* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [field_name_1] = `city.raw`
    * [operator_1] = `=`
    * [value_1] = `Paris`
    * [field_name_2] = `entity.raw`
    * [operator_2] = `=`
    * [value_2] = `Adimeo`  
    
    * **Call:**      
    Don't forget `-g` option because of brackets:
    ```console
        curl -g http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&filter\[\]\=city.raw%3D%22Paris%22\&filter\[\]\=entity.raw%3D%22Adimeo%22
    ```
  
    * **Code:** 200
  
    * **Content:** 
      ```json  
          {
            "took": 1,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 1,
                "max_score": 5,
                "hits": [
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJQs5a6YCxlRadcd",
                        "_score": 5,
                        "_source": {
                            "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                            "city": "Paris",
                            "entity": "Adimeo",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                            "url": "https:\/\/adimeo.com",
                            "color": "rouge"
                        }
                    }
                ]
            }
          } 
      ```      

<a name="search-query-string"></a>
#### Search: Query string on analyzed field
Return all the data constrained by a filter in connection with an analyzed field.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&qs_filter[]=`[field_name_1]="[value_1]"`&qs_filter[]=`[field_name_2]="[value_2]"...
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `qs_filter[]=[array]`: Defines which filter must be applied in regard <br /> 
    Each filter must respect the following pattern: `[field name]`="`[value]`".<br /> 
  
* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [field_name_1] = `city`
    * [value_1] = `Paris`
    * [field_name_2] = `entity`
    * [value_2] = `Adimeo`
    
    * **Call:**    
        Don't forget `-g` option because of brackets:
      ```console
        curl -g http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&qs_filter\[\]\=city%3D%22Paris%22\&qs_filter\[\]\=entity%3D%22Adimeo%22
      ```

    * **Code:** 200
  
    * **Content:** <br />  
      ```json  
        {
            "took": 2,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 1,
                "max_score": 6.3862944,
                "hits": [
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJQs5a6YCxlRadcd",
                        "_score": 6.3862944,
                        "_source": {
                            "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                            "city": "Paris",
                            "entity": "Adimeo",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                            "url": "https:\/\/adimeo.com",
                            "color": "rouge"
                        }
                    }
                ]
            }
        } 
      ```  
  
<a name="activation-auto-promote"></a>
#### Search: Display auto-promote
Display the promotional banners previously defined for a target (index.mapping) that appear at the top of search results 
based on the keywords provided.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&autopromote=1`
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `autopromote=[integer]`: Activation of auto-promote for the target index.mapping concerned. 
  
* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [autopromote] = `1` 

    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&autopromote\=1
    ``` 

    * **Code:** 200
    
    * **Content:** <br /> 
      ```json
        {
            "took": 1,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 1,
                "max_score": 2.7330778,
                "hits": [
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJQs5a6YCxlRadcd",
                        "_score": 2.7330778,
                        "_source": {
                            "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                            "city": "Paris",
                            "entity": "Adimeo",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                            "url": "https:\/\/adimeo.com",
                            "color": "rouge"
                        }
                    }
                ]
            },
            "autopromote": [
                {
                    "title": "Adimeo acqui\u00e8re OpCoding",
                    "body": "Test de contenu",
                    "url": "https:\/\/www.adimeo.com",
                    "image": null
                }
            ]
        }
      ```

<a name="search-sort-filter"></a>
#### Search: Sort filter
You can specify the order filter on a **raw or transliterated** field.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&sort[]=`[field_name],[your_order]
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `sort[]=[string]`: Defines which field must be sort by (ASC or DESC)<br /> 
    The sort filter must respect the following pattern: `[field_name]`,`[ASC|DESC]`".<br />   
  
* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [field_name] = `entity.raw`
    * [your_order] = `DESC`

    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&sort\=entity.raw,DESC
    ```

    * **Code:** 200
    
    * **Content:** <br /> 
      ```json
          {
            "took": 2,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 3,
                "max_score": null,
                "hits": [
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHI0E5a6YCxlRadcQ",
                        "_score": null,
                        "_source": {
                            "body": "Nous sommes  l\u054ecoute de vos problmatiques pour dterminer laccompagnement le plus pertinent en fonction de votre contexte, de vos dlais et de votre budget. Contactez-nous pour connatre nos tarifs et disponibilits.",
                            "city": "Metz",
                            "entity": "Opcoding",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=1079",
                            "url": "https:\/\/www.opcoding.eu",
                            "color": "bleu"
                        }
                    },
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJV45a6YCxlRadce",
                        "_score": null,
                        "_source": {
                            "body": "Notre agence inbound marketing met en place des dispositifs de contenus performants pour aider PME, ETI et Grands groupes  gnrer des leads de faon plus directe et moins coteuse.",
                            "city": "Paris",
                            "entity": "Comexplorer",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=134",
                            "url": "https:\/\/www.comexplorer.com",
                            "color": "vert"
                        }
                    },
                    {
                        "_index": "api_demo",
                        "_type": "data_demo",
                        "_id": "AWhxHJQs5a6YCxlRadcd",
                        "_score": null,
                        "_source": {
                            "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                            "city": "Paris",
                            "entity": "Adimeo",
                            "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                            "url": "https:\/\/adimeo.com",
                            "color": "rouge"
                        }
                    }
                ]
            }
          }
      ```        

<a name="search-suggestion-field"></a>
#### Search: Suggestion fields
If your main search failed for the current channel, a suggestion, based on the suggestion fields, will be proposed:
*Did you mean... ?*

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&suggest[]=`[field_name_1],[field_name_2]
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `suggest[]=[string]`: Defines which field is concerned by suggestion.<br /> 
    The suggestion parameter must respect the following pattern: `[field_name_1]`,`[field_name_2]`".<br />   
  
* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [field_name_1] = `entity`
    * [field_name_2] = `city`    

    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&suggest\=entity,city\&query=Ademo
    ```

    * **Code:** 200
    
    * **Content:** <br /> 
    The main query with the string "Ademo" does not have any result. A suggestion has been found in entity field and "Adimeo" has been proposed.
      ```json
        {
            "took": 5,
            "timed_out": false,
            "_shards": {
                "total": 5,
                "successful": 5,
                "skipped": 0,
                "failed": 0
            },
            "hits": {
                "total": 0,
                "max_score": null,
                "hits": []
            },
            "suggest": {
                "city": [
                    {
                        "text": "ademo",
                        "offset": 0,
                        "length": 5,
                        "options": []
                    }
                ],
                "entity": [
                    {
                        "text": "ademo",
                        "offset": 0,
                        "length": 5,
                        "options": [
                            {
                                "text": "adimeo",
                                "score": 0.6,
                                "freq": 1
                            }
                        ]
                    }
                ]
            },
            "suggest_ctsearch": [
                {
                    "field": "entity",
                    "text": "adimeo",
                    "score": 0.6,
                    "freq": 1
                }
            ]
        }
      ```   

<a name="search-highlights"></a>
#### Search: Highlights
You can highlight the search terms if those ones were found in specified fields.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&query=`[your_term]`&highlights=`[your_highlight_params]`
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `query=[string]`: Characters to search in result.  
  * `highlights=[string]`: The following pattern must be respected: 
    `[field]|[fragment_size]|[number_of_fragments]|[no_match_size],[field]|[fragment_size]|[number_of_fragments]|[no_match_size]`.
  
  * **field**<br />
  Specifies the fields to retrieve highlights for. You can use wildcards to specify fields. 
  For example, you could specify comment_* to get highlights for all text and keyword fields that start with comment_.
  Only text and keyword fields are highlighted when you use wildcards. 
  If you use a custom mapper and want to highlight on a field anyway, you must explicitly specify that field name.
  
  * **fragment_size**<br />
  The size of the highlighted fragment in characters. Defaults to 100.
  
  * **number_of_fragments**<br />
  The maximum number of fragments to return. If the number of fragments is set to 0, no fragments are returned. Instead, the entire field contents are highlighted and returned. This can be handy when you need to highlight short texts such as a title or address, but fragmentation is not required. If number_of_fragments is 0, fragment_size is ignored. Defaults to 5.
  
  * **no_match_size**<br />
  The amount of text you want to return from the beginning of the field if there are no matching fragments to highlight. Defaults to 0 (nothing is returned).  
  
* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [your_term] = `Adimeo`
    * [your_highlight_params] = `entity|100|10|9999,body|200|3|300`    

    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&query\=Adimeo\&highlights\=entity\|100\|10\|9999,body\|200\|3\|300
    ```

    * **Code:** 200
    
    * **Content:** <br /> 
      ```
          ...
            "highlight": {
                "body": [
                    "<em>Adimeo<\/em> s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme"
                ],
                "entity": [
                    "<em>Adimeo<\/em>"
                ]
            }
          ...         
      ```  

<a name="search-facets"></a>
#### Search: Facets
You can get the consolidation and the associated count of all the main terms of the specified fields (**raw or transliterated**) 
for the relevant index. 

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&facets=`[your_raw_field_1],[your_raw_field_2]
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `facets=[string]`: Defines which raw or transliterated field is concerned to faceted.
    The following pattern must be respected: `[your_raw_field_1],[your_raw_field_2]`.
  
* **Example :**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [your_raw_field_1] = `entity.raw` 
    * [your_raw_field_2] = `city.raw` 

    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&facets\=entity.raw,city.raw
    ```  

    * **Code:** 200
    
    * **Content:** <br /> 
      ```
       ...
        "aggregations": {
            "city.raw": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": [
                    {
                        "key": "Paris",
                        "doc_count": 2
                    },
                    {
                        "key": "Metz",
                        "doc_count": 1
                    }
                ]
            },
            "entity.raw": {
                "doc_count_error_upper_bound": 0,
                "sum_other_doc_count": 0,
                "buckets": [
                    {
                        "key": "Adimeo",
                        "doc_count": 1
                    },
                    {
                        "key": "Comexplorer",
                        "doc_count": 1
                    },
                    {
                        "key": "Opcoding",
                        "doc_count": 1
                    }
                ]
            }
        }
        ...
      ```           

<a name="search-analyzer"></a>
#### Search: Index analyzer
You can change your index analyzer instead of the default one `standard` :

* **Standard**<br />
The standard analyzer divides text into terms on word boundaries, as defined by the Unicode Text Segmentation algorithm. 
It removes most punctuation, lowercases terms, and supports removing stop words.

* **Simple**<br />
The simple analyzer divides text into terms whenever it encounters a character which is not a letter. It lowercases all terms.

* **Whitespace**<br />
The whitespace analyzer divides text into terms whenever it encounters any whitespace character. It does not lowercase terms.

* **Stop**<br />
The stop analyzer is like the simple analyzer, but also supports removal of stop words.

* **Keyword**<br />
The keyword analyzer is a “noop” analyzer that accepts whatever text it is given and outputs the exact same text as a single term.

* **Pattern**<br />
The pattern analyzer uses a regular expression to split the text into terms. It supports lower-casing and stop words.

* **Language**<br />
Elasticsearch provides many language-specific analyzers like `english` or `french`.

* **Fingerprint**<br />
The fingerprint analyzer is a specialist analyzer which creates a fingerprint which can be used for duplicate detection. 

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&analyzer=`[your_analyzer]
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `analyzer=[string]`: Defines which analyzer must be applied to the current index. 
  
* **Example :**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [analyzer] = `whitespace` 

    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&analyzer\=whitespace
    ```  

<a name="activation-boost-query"></a>
#### Search: Boost query
The boosting query can be used to effectively demote results that match a given query. 
These make it possible to adjust the relevance of the engine according to certain criteria (Ex: to make up the priority recent contents).

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`&apply_boosting=1`
  
* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `apply_boosting=[integer]`: Activation of boost query configured for a target (index.mapping). 
  
* **Example :**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [apply_boosting] = `1` 

    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&apply_boosting\=1
    ``` 
        
<a name="autocomplete"></a>
### Autocomplete
Returns json data corresponding to the partial or complete text from the parameterized data field.

* **URL**

  `/search-api/v2/autocomplete?mapping=`[your_index].[your_mapping]`&field=`[your_field_name]`&group=`[your_field_name_for_group_of_results]`
  &size=`[nb_result_autocomplete]`&sizePerGroup=`[nb_group_for_result_autocomplete]`&text=`[text_to_autocomplete]`

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `field=[string]`: Defines the data field used as the source for auto-completion. 
    The field format must be `keyword` or `text with included raw`.
  * `text=[string]`: Characters to search in the specified data field.
   
  **Optional:**
    
  * `group=[string]`: Determine the data field for categorizing the results of auto-completion. 
    The field format must be `keyword` or `text with included raw`.
  * `size=[integer]`: Determine the number of displaying results for the field name for a group or not. 
    By default, this number is set to `20`.
  * `sizePerGroup=[integer]`: Determine the number of displaying group. 
    By default, this number is set to `10`.

* **Success Response:**

  * **Code:** 200
  
  * **Content:** 
  
    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [your_field_name] = `entity.raw`
    * [your_field_name_for_group_of_results] = `city.raw`
    * [nb_result_autocomplete] = `5`
    * [nb_group_for_result_autocomplete] = `4`
    * [text_to_autocomplete] = `ad`
    
    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2/autocomplete\?mapping\=api_demo.data_demo\&field\=entity.transliterated\&group\=city.raw\&text\=ad
    ``` 

    * **Code:** 200    
      
    * **Content:** <br />       
        ***With optional param `group` when result is found:***
        
          ```json
            {
                "grouped": true,
                 "results": {
                     "Paris": [
                         "Adimeo"
                     ]
                 }
               }                 
          ``` 
      
        ***With no optional param `group` when result is found:***
        
          ```json    
            {   
                "grouped": false,
                "results": [
                    "Adimeo"
                ]
            }           
          ```
  
<a name="see-more-like-this"></a>
### See more like this  
Find documents that are similar to a given document or a set of documents.

* **URL**

   `search-api/v2/more-like-this?mapping=`[your_index].[your_mapping]`&fields=`[your_fields_concerned]`&doc_id=`[your_document_id]

* **Method:**

    `GET`

* **URL Params**

  **Required:**
   
  * `mapping=[string]`: Defines which index and mapping is concerned. 
    The following pattern must be respected: `[your_index].[your_mapping]`.
  * `fields=[array of string]`: Defines the data fields for similarity search
  * `doc_id=[string]`: ID of the reference document 
  
* **Success Response:**
  
    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [your_fields_concerned] = `entity, body`
    * [your_document_id] = `AWiUunlU5a6YCxlRbh4B` [Adjust this value for your documents]
  
    * **Call:**    
    ```console
        curl http://localhost:8888/index.php/search-api/v2/more-like-this\?mapping\=api_demo.data_demo\&fields\=entity,body\&doc_id\=AWiUunlU5a6YCxlRbh4B
    ```   
    * **Code:** 200
    
    * **Content:** 
    This result for this dataset can be obtained by fixing `min_doc_freq` to `1`.
    
<a name="custom-search"></a>
### Custom search
Make your own and custom query by-passing ADS filter.

* **URL**

    `search-api/v2/custom?index=`[your_index]`&size=`[your_size]

* **Method:**

    `POST`

* **URL Params**

    **Required:**

    * `index=[string]`: Defines which index is concerned.
    * `size=[integer]`: Pagination of results : Defines the maximum amount of hits to be returned.
    * Define query in the body

    **Optional:**    

    * `from=[integer]`: Pagination of results : Defines the offset from the first result you want to fetch. Default value `0`.        
    * `type=[string]`: Default value `null (query_then_fetch)`. Values possibles : `query_then_fetch`|`dfs_query_then_fetch`.

* **Success Response:**

    Let's try to make an sample call cURL with this parameters:
    * [your_index] = `api_demo`
    * [your_size] = `entity.raw`
    * [your_raw_data] = `{"query":{"bool":{"must":{"term":{"entity":"adimeo"}}}}}` 
    
    * **Call:**    
    ```console
        curl -d '{"query":{"bool":{"must":{"term":{"entity":"adimeo"}}}}}' -H "Content-Type: application/json" -X POST http://localhost:8888/index.php/search-api/v2/custom\?index\=\api_demo\&size\=10
    ``` 

    * **Code:** 200
    
    * **Content:**    
    ```json
    {
        "took": 12,
        "timed_out": false,
        "_shards": {
            "total": 33,
            "successful": 33,
            "skipped": 0,
            "failed": 0
        },
        "hits": {
            "total": 1,
            "max_score": 0.6931472,
            "hits": [
                {
                    "_index": "api_demo",
                    "_type": "data_demo",
                    "_id": "AWhxHJQs5a6YCxlRadcd",
                    "_score": 0.6931472,
                    "_source": {
                        "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                        "city": "Paris",
                        "entity": "Adimeo",
                        "thumbnail": "https://picsum.photos/200/300/?image=921",
                        "url": "https://adimeo.com",
                        "color": "rouge"
                    }
                }
            ]
        }
    }    
    ``` 