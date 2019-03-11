To run the unit and functional tests:

```
make test
```

Workflow:
  - Message is created from Request object
  - Message is validated
  - Message is logged
  - Message is handled by MessageHandler service
  - Return value from handler is serialized into a Response object

Highlights:
- Only `src/Message` and `src/MessageHandler` are application-specific
- Routing is derived from annotations in `src/Message`, see `config/routes.yaml`
- Input validation is derived from annotations in `src/Message`
