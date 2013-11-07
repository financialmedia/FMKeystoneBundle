CHANGELOG
=========

## 0.3.0

### Breaking changes
The services logic was refactored: before this release you had to create a
service by persisting an entity containing the name/type and endpoints. However
this really is configuration, not data. So you can now define your services in
the Symfony configuration file. Multiple notations are supported, below is an
example demonstrating all of them:

```yaml
fm_keystone:
  user_class: FM\KeystoneBundle\Entity\User
  user_provider_id: fm_keystone.security.user_provider.username_email
  services:
    # Shortest notation: just a simple type/url. The public and admin url will
    # be the same.
    api:
      type: compute
      endpoint: https://api.example.org/

    # Same as above, but with multiple endpoints
    api2:
      type: compute
      endpoint:
        - http://api.example.org/
        - https://api.example.org/

    # A simple endpoint but with different public/admin urls
    cdn:
      type: object-store
      endpoint: { public_url: http://examplecdn.org/, admin_url: https://admin.example.org/ }

    # Same as above, but supplied as an array
    cdn2:
      type: object-store
      endpoint:
        - { public_url: http://examplecdn.org/, admin_url: https://admin.example.org/ }

    # Same as above, but supplied as an array, with multiple endpoints
    cdn3:
      type: object-store
      endpoint:
        -
          public_url: http://cdn.example.org/
          admin_url: https://admin.example.org/
        -
          public_url: http://examplecdn.org/
          admin_url: https://admin.examplecdn.org/
```

Because of this change, the [ServiceManager](/src/FM/KeystoneBundle/Manager/ServiceManager.php)
has some renamed or removed methods:

* Renamed `findAll()` to `getServices()`
* Removed `findServiceBy()`
* Removed `findServiceById()`
* Removed `createService()`
* Removed `addEndpoint()`
* Removed `updateService()`
* Removed `removeService()`

Also, the following commands are removed:

* `keystone:service:create`
* `keystone:service:remove`

The `Service` and `Endpoint` entities have been removed, so any tables will be
removed once a schema update is executed. The [model classes](/src/FM/KeystoneBundle/Model) however are
preserved, since we do want to work with them as objects.


## 0.2
Initial release, pretty much alpha state.
