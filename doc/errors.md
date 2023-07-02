# List of development errors

This is just a list of errors that I have encountered during development. By keeping traces of them, they will be easier to avoid in the future.

Software developemnt is a slow process even for an experimented developer. The biggest factor which slows it down, and maybe the point which can be improved, it all the cases when the software does not behave as expected.

This is an analysis of the time spent to wonder why something does not work compared to the time used to tackle an issue after another and progression regularly toward the objective.

It may be because of bugs. Note that most bugs fixed during the development process are even not called bugs. They are just uncomplete states of the software. Even with frequent commits they are fixed before the commit. However time to diagnostic them can be significant especially that they are frequent.

Other cases are environment issues, corrupted databases, incorrect deployments, wrong environment. Some of them maybe plenty stupid like starting to debug a modification that does not work just to discover that you forgot to save a file.

Most of the time these issues are a discrepency betwwen your mental model of the reality and the reality.

Numerous reasons:

* raw ignorance, you just do not know how to do something.
* ignorance of a detail, you know how to do something but you missed a point and now you have to do a whole diagnostic campaign to find out the reason of the problem.
* Abstraction leaking, you develop at some abstraction level but something at a lower level of abstraction is making problems (hardware failure, memory corruption, down server, running our of resources, etc.). Note that you usually do dot have to most appropriate tools to work on these issues.

## wrong naming

Something has not the correct name inside a routine. In fact I reused the name used outside the routine and not the parameter name.

Solution : be consistent on naming across places.

## forgot to return the result ..
Several times ...
nothing to say.
  
## Syntax errors

* missing parenthesis
* extra brackets

(usually signalled in red by VSC)

## Test interface

attempt to click on something not visible

## Call of the wrong method
"type" instead of "select"

## Context
use of $this in the wrong context

## Invisible characters

Non breaking spaces inside an input field.

## Exact comparison on float

Rounding has to be taken into account

## Blank page 

    http://localhost/gvv2/index.php/comptes/general

    in index.php
        define('ENVIRONMENT', 'development');

    Fatal error: Uncaught Error: Call to a member function row() on bool in C:\Users\frede\Dropbox\xampp\htdocs\gvv2\application\models\ecritures_model.php on line 423

    select * from ecritures,comptes
    #1194 - La table 'ecritures' est marquée 'crashed' et devrait être réparée

## Dusk tests cannot run in development mode

When index.php is not in production mode, the warnings on the display disturb the tests.

## Element not yet displayed, page not loaded

use $browser->waitFor('.selector');

