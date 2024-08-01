# GVV test list

## Existing tests

### Installation test

Check the installation page, the reset and the database migration

### Page access

This tests checks that the pages can be accessed or not according to the user role.

* Admin
* Bureau
* CA
* Planchiste
* User

* Accounter (TBD)

### CI Unit tests

Run the CI Unit tests and checks that they do not report errors.

## Smoke test

Basic flight creation and billing.
Accounting lines creation.

## Backup Restore

The restore function is used to initialize the tests.

## Flight tests

Detailled tests of flights.
* CRUD
* Rejected flights
* Check of the billing after several cases of flights, both after creation and modification. Especially checks that pilots are reimbursed after flight cancellation or modifications.
* Shared costs tests.
  
## Tests to develop

* Accounting tests
    * Accounting lines CRUD
      * impact on accounts
      * Sales and payments
      *  
    * End of year reports
    * End of year operations
    * Freeze date
  
* Emails and forgotten password
* CRUD for all resources