# Adimeo Data Suite: Documentation Search API

1. [ Search ](#search)
    1. [ Search : Get all results ](#search-all-results)
    1. [ Search : Get a specific document ](#search-specific-document)    
    1. [ Search : Advanced Search ](#search-advanced-search)     
2. [ Autocomplete ](#autocomplete)
----
<a name="search"></a>
<a name="search-all-results"></a>
## Search : Get all results
Returns all the data for an index and a mapping

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]` : Defines which index and mapping is concerned. The following pattern must be respected : `[your_index].[your_mapping]` ;

* **Success Response:**

  * **Code:** 200 <br />
  * **Content:** <br />
  ```json  
  {
      "took": 9,
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
                  "_id": "AWhhcBWdRyRzLu-J96Kb",
                  "_score": 5,
                  "_source": {
                      "body": "Nous sommes  l\u054ecoute de vos problmatiques pour dterminer laccompagnement le plus pertinent en fonction de votre contexte, de vos dlais et de votre budget. Contactez-nous pour connatre nos tarifs et disponibilits.",
                      "city": "Metz",
                      "entity": "Opcoding",
                      "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=1079",
                      "url": "https:\/\/www.opcoding.eu"
                  }
              },
              {
                  "_index": "api_demo",
                  "_type": "data_demo",
                  "_id": "AWhhcB33RyRzLu-J96Kc",
                  "_score": 5,
                  "_source": {
                      "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                      "city": "Paris",
                      "entity": "Adimeo",
                      "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                      "url": "https:\/\/adimeo.com"
                  }
              },
              {
                  "_index": "api_demo",
                  "_type": "data_demo",
                  "_id": "AWhhcB8zRyRzLu-J96Kd",
                  "_score": 5,
                  "_source": {
                      "body": "Notre agence inbound marketing met en place des dispositifs de contenus performants pour aider PME, ETI et Grands groupes  gnrer des leads de faon plus directe et moins coteuse.",
                      "city": "Paris",
                      "entity": "Comexplorer",
                      "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=134",
                      "url": "https:\/\/www.comexplorer.com"
                  }
              }
          ]
      }
  }
  ```
* **Detailed example:**

    Let's try to make an sample call cURL with this parameters :
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`

    ****Call:****    
  ```console
    curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo
  ```
    ****Result:****  
  ```json  
  {
      "took": 9,
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
                  "_id": "AWhhcBWdRyRzLu-J96Kb",
                  "_score": 5,
                  "_source": {
                      "body": "Nous sommes  l\u054ecoute de vos problmatiques pour dterminer laccompagnement le plus pertinent en fonction de votre contexte, de vos dlais et de votre budget. Contactez-nous pour connatre nos tarifs et disponibilits.",
                      "city": "Metz",
                      "entity": "Opcoding",
                      "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=1079",
                      "url": "https:\/\/www.opcoding.eu"
                  }
              },
              {
                  "_index": "api_demo",
                  "_type": "data_demo",
                  "_id": "AWhhcB33RyRzLu-J96Kc",
                  "_score": 5,
                  "_source": {
                      "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                      "city": "Paris",
                      "entity": "Adimeo",
                      "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                      "url": "https:\/\/adimeo.com"
                  }
              },
              {
                  "_index": "api_demo",
                  "_type": "data_demo",
                  "_id": "AWhhcB8zRyRzLu-J96Kd",
                  "_score": 5,
                  "_source": {
                      "body": "Notre agence inbound marketing met en place des dispositifs de contenus performants pour aider PME, ETI et Grands groupes  gnrer des leads de faon plus directe et moins coteuse.",
                      "city": "Paris",
                      "entity": "Comexplorer",
                      "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=134",
                      "url": "https:\/\/www.comexplorer.com"
                  }
              }
          ]
      }
  }
  ```
  
<a name="search-specific-document"></a>
### Search : Get a specific document
Returns all datas from a document specified by its ID.

* **URL**
  `/search-api/v2?mapping=`[your_index].[your_mapping]`&doc_id=`[you_document_id]

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]` : Defines which index and mapping is concerned. The following pattern must be respected : `[your_index].[your_mapping]`  
  * `doc_id=[string]` : ID of the document to display   

* **Success Response:**

  * **Code:** 200 <br />
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
            "max_score": 1,
            "hits": [
                {
                    "_index": "api_demo",
                    "_type": "data_demo",
                    "_id": "AWhR2nKlRyRzLu-J9db3",
                    "_score": 1,
                    "_source": {
                        "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                        "city": "Paris",
                        "entity": "Adimeo",
                        "thumbnail": "https:\/\/picsum.photos\/g\/200\/300",
                        "url": "https:\/\/adimeo.com"
                    }
                }
            ]
        }
   }
  ```  
    
* **Detailed example:**

    Let's try to make an sample call cURL with this parameters :
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [you_document_id] = `AWhhcB33RyRzLu-J96Kc`

    ****Call:****    
  ```console
    curl http://localhost:8888/index.php/search-api/v2\?mapping\=api_demo.data_demo\&doc_id\=AWhhcB33RyRzLu-J96Kc
  ```
    ****Result:****  
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
            "max_score": 1,
            "hits": [
                {
                    "_index": "api_demo",
                    "_type": "data_demo",
                    "_id": "AWhhcB33RyRzLu-J96Kc",
                    "_score": 1,
                    "_source": {
                        "body": "Adimeo s'engage  vos cts pour concevoir les plateformes digitalesles plus percutantes et efficaces. Nous imaginons avec vous les dispositifs les plus adapts et leur intgration dans votre cosystme digital. Qu'il s'agisse de vous accompagner dans ladfinition stratgiquede votre positionnement digital, dans la mise en oeuvre d'uneexprience utilisateur efficace, ou dans le dploiement dedispositifs d'acquisition, nos quipes pluridisciplinaires (stratgie, ux, design, content et acquisition)vous proposent un accompagnement au quotidien.",
                        "city": "Paris",
                        "entity": "Adimeo",
                        "thumbnail": "https:\/\/picsum.photos\/200\/300\/?image=921",
                        "url": "https:\/\/adimeo.com"
                    }
                }
            ]
        }
   }
  ```

<a name="search-advanced-search"></a>
## Search : Advanced search
Returns all the data corresponding to the search text that can be constrained by search facets.
In the case where no result is found, an attempt to correct automatically from the corpus (no dictionary use) is performed.

* **URL**

  `/search-api/v2?mapping=`[your_index].[your_mapping]`

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]` : Defines which index and mapping is concerned. The following pattern must be respected : `[your_index].[your_mapping]` ;


<a name="autocomplete"></a>
## Autocomplete

Returns json data corresponding to the partial or complete text from the parameterized data field.

* **URL**

  `/search-api/v2/autocomplete?mapping=`[your_index].[your_mapping]`&field=`[your_field_name]`&group=`[your_field_name_for_group_of_results]`
  &size=`[nb_result_autocomplete]`&sizePerGroup=`[nb_group_for_result_autocomplete]`&text=`[text_to_autocomplete]`

* **Method:**

  `GET`
  
* **URL Params**

  **Required:**
   
  * `mapping=[string]` : Defines which index and mapping is concerned. The following pattern must be respected : `[your_index].[your_mapping]` ;
  * `field=[string]` : Defines the data field used as the source for auto-completion. The field format must be `keyword` or `text with included raw` ;
  * `text=[string]` : Characters to search in the specified data field.
   
  **Optional:**
    
  * `group=[string]` : Determine the data field for categorizing the results of auto-completion. The field format must be `keyword` or `text with included raw`.
  * `size=[integer]` : Determine the number of displaying results for the field name for a group or not. By default, this number is set to `20`.
  * `sizePerGroup=[integer]` : Determine the number of displaying group. By default, this number is set to `10`.

* **Success Response:**

  * **Code:** 200 <br />
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
* **Detailed example:**

    Let's try to make an sample call cURL with this parameters :
    * [your_index] = `api_demo`
    * [your_mapping] = `data_demo`
    * [your_field_name] = `entity.raw`
    * [your_field_name_for_group_of_results] = `city.raw`
    * [nb_result_autocomplete] = `5`
    * [nb_group_for_result_autocomplete] = `4`
    * [text_to_autocomplete] = `ad`
    
    ****Call:****    
  ```console
    curl http://localhost:8888/index.php/search-api/v2/autocomplete\?mapping\=api_demo.data_demo\&field\=entity.transliterated\&group\=city.raw\&text\=ad
  ```
  ****Result:****  
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