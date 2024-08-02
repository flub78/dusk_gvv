# Tests organization

It is sometimes complex to find a good tradeoff between several tests characteristics.

* Tests independence: Tests should be self contained and do not rely on each others.
* Efficiency: Tests should be fast, things should be tested only once.
* Tests should be simple.
* Tests should be robust, they should only rely on the input conditions that mater for the test. For example checking that you can create an element in database should be possible whatever the number of existing elements in the table.
* Tests should cleanup after themselves. They should not create and keep new test data after each run.

The first principles would push to create the text context before every test. Note that when the test implies complex features a full database could have to be restored before test.

The second one would push to reuse contexts.


