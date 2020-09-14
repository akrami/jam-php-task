# Invitation API System

This is a task done for JAM

## API Endpoints
### `GET /` : welcome message
### `POST /login` : login and get jwt token
user credentials must be sent with json body

    {"username": "admin", "password": "admin"}
    
for endpoints that need to be authenticated, one should add jwt token to the `AUTHORIZATION` header or send as `token` variable through GET

### `GET /users` : get all users
### `POST /users` : add new user
    {"username": "user", "password": "pass", "email": "valid@email.com"}
    
### `GET /users/:id` : get a user
### `POST /users/:id/invite` : invite another user
    {"name": "invitation's name", "description": "is optional"}
    
### `GET /invitations` : get all my sent invitations
### `GET /invitations/pending` : invitations I have been invited
### `GET /invitations/:id` : get an invitation
### `DELETE /invitations/:id` : cancel an invitation
### `PUT /invitations/:id` : respond to an invitation
    {"response": true}
    
## Database Migration
please run `php builder.php` on root

## Run
rename `.env.example` to `.env` and replace your configuration.

the simplest way to run `php -S localhost:3030`
### Development Environment
    php ^7.1
    MySQL ^8.0
    
## Test
please run app before testing on port `3030`