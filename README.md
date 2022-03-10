# Maboo Maker Bundle

Boilerplate code generator for projects with layered architecture

## Installation
To install this package, use Composer:
```shell
composer require bornfight/maboo-maker-bundle --dev
```
If you are using [Symfony Flex](https://symfony.com/doc/current/setup.html#symfony-flex), this will install and enable the Maboo Maker bundle automatically.  
If not, you should add this manually to your `config/bundles.php` file:
```php
return [
    // ...
    Bornfight\MabooMakerBundle\BornfightMabooMakerBundle::class => ['dev' => true, 'test' => true],
];
```

# Usage
There are multiple commands available which can work independently, but whenever possible, you can make your life easier just by running:
```bash
bin/console make:maboo-scaffold
```

**List of currently supported sub-commands:**
```bash
make:maboo-module        # Creates a bounded context (module) folder (if it does not exist yet)                       
make:maboo-entity        # Creates or updates a Doctrine entity class                                               
make:maboo-domain-model  # Creates or updates a domain model class                                                  
make:maboo-write-models  # Creates or updates write models for a model class                                        
make:maboo-entity-mapper # Creates or updates mapper for a model class                                              
make:maboo-repository    # Creates or updates a repository interface and concrete implementation                    
make:maboo-validator     # Creates or updates a validator and specification                                         
make:maboo-manager       # Creates or updates a resource manager                                                    
make:maboo-resolver      # Creates or updates a resolver                                                            
make:maboo-mutation      # Creates or updates a mutation class          
make:maboo-fixtures      # Creates or updates a fixtures class with some dummy data                                                                                              
make:maboo-gql-schema    # Creates or updates GraphQL types and schema 
```
`make:maboo-scaffold` will start the interactive wizard and ask you which of the components you need and then internally execute all selected commands one by one.

## Happy path
### Generating a new entity with scalar types for fields
1. Run `bin/console make:maboo-scaffold`
2. Follow the interactive wizard:
   1. Select all available options (this is the default so just press <kbd>↵ Return</kbd> to confirm) 
   2. Select an existing module or create a new one. Existing folders within project source directory (`src/`) are suggested, so you can use <kbd>⇥ Tab</kbd> for autocompletion.  
   Example: `Booking`
   3. Type entity name (for now, existing entities are also suggested when you start typing, but updating is currently not supported).  
   Example: `Hotel`  
   This creates a class in `src/Shared/Infrastructure/Persistence/Doctrine/Entity`
   4. All classes will have this entity name suggested, but you can overwrite any of those. If you would like to keep the defaults, just confirm it by pressing <kbd>↵ Return</kbd>:
      1. **Domain model** name: `Hotel`  
      This creates a class in `src/Booking/Domain/Model`
      2. **Entity mapper** name: `HotelMapper`  
      This creates a class in `src/Shared/Infrastructure/Persistence/Doctrine/Mapper`
      3. **Write model** names: `CreateHotel` and `UpdateHotel`  
      This creates classes in `src/Booking/Domain/WriteModel`
      4. **Repository** interface and class names: `HotelRepository` and `DoctrineHotelRepository`  
      This creates an interface in `src/Booking/Domain/Repository` and a class in `src/Booking/Infrastructure/Persistence/Repository`
      5. Basic **specification** interface and class names: `IsExistingHotelSpecification` and `DoctrineIsExistingHotelSpecification`  
      This creates an interface in `src/Booking/Application/Specification` and a class in `src/Booking/Infrastructure/Specification`
      6. **Validator** name: `HotelValidator`  
      This creates a class in `src/Booking/Application/Validator`
      7. **Manager** name: `HotelManager`  
      This creates a class in `src/Booking/Application/Manager`
      8. **Resolver** name: `HotelResolver`  
      This creates a resolver class in `src/Booking/Infrastructure/GraphQL/Resolver`
      9. **Mutation** name: `HotelMutation`  
      This creates a mutation class in `src/Booking/Infrastructure/GraphQL/Mutation`
      10. **Fixtures** name: `HotelFixures`  
      This creates a fixtures class in `src/Booking/Infrastructure/Persistence/DataFixtures`
   5. Add fields:  
   Example: `name` (string, 255, non-nullable), `isOpen` (boolean, non-nullable), `address` (string, 255, non-nullable), `longitude` (float, nullable), `latitude` (float, nullable)
   6. Press <kbd>↵ Return</kbd> one more time.  
3. Generate and apply migrations (these are Doctrine commands and have nothing to do with the generator):
   1. `bin/console make:migration`
   2. `bin/console doctrine:migration:migrate`
4. Add some meaningful data to the fixtures or just load dummy data pregenerated based on field types:
   1. `bin/console doctrine:fixtures:load`
5. Generate GraphQL schema: 
   1. Run `bin/console make:maboo-gql-schema`
   2. Select the module (`Booking`) and the entity you've just generated (`Hotel`)  
   This updates existing `Query.types.yaml` and `Mutation.types.yaml` files in directory `/config/graphql/types`.  
   This also creates GraphQL input, payload and type schema files in `src/Booking/Infrastructure/Resources/config/graphql/types`
6. 🚀 All done!

Now try running a query in your favourite GraphQL GUI:
```yaml
query {
  hotels {
    id
    name
    isOpen
    address
    longitude
    latitude
  }
}
```

You should get something like: 
```json
{
  "data": {
    "hotels": [
      {
        "id": "a89b8a17-3b15-4a23-9ad0-67229f13fc18",
        "name": "example",
        "isOpen": true,
        "address": "example",
        "longitude": 20.5,
        "latitude": 20.5
      }
    ]
  }
}
```

It works!

Try running a mutation (by default, you must have admin rights):
```yaml
mutation {
  createHotel(input: {
    name: "Westin"
    isOpen: false
    address: "Izidora Kršnjavoga 1, 10000 Zagreb"
    longitude: 45.80689045084249
    latitude: 15.9662605177903
  }) {
    hotel {
      id
    }
  }
}
```
The response should contain the ID of the newly created hotel:
```json
{
  "data": {
    "createHotel": {
      "hotel": {
        "id": "061a7975-4334-4d00-9a00-4243ac4a9726"
      }
    }
  }
}
```

# Motivation
This bundle should make creating a bunch of files with a bunch of boilerplate code a cinch.  
Copy-pasting existing entities and models and then renaming just some fields can be cumbersome and error-prone task. It is also time-consuming task and makes you feel like a code monkey. 
We use some strict rules and instead of looking for analogies in existing classes and lots of manual work, this generator does that for you.

The very implementation was heavily influenced by Symfony's [Maker Bundle](https://symfony.com/bundles/SymfonyMakerBundle/current/index.html). Some parts of the code in it are literally a copy-paste because mentioned bundle has classes declared as `final` which makes it impossible to extend them and overwrite just some parts of the logic.  

