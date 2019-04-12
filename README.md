# README #

Informed 365 API

### What is this repository for? ###

* Storage and development of Informed 365 API
* 1.0
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### How do I get set up? ###

* Get a copy of the repo

### Contribution guidelines ###

This is to assist in making sure all code is developed to a set standard moving forward. We will go through and refactor the code at a later date however all future code for lumen should be written in this format:

#### Controllers ####

* Controllers should be simple and not contain any business logic or validation
* Controllers should only call service functions and return a response, no additional logic.
* Controllers should contain the default laravel methods
    * index
    * create
    * store
    * show
    * edit
    * update
    * destroy
* Controllers which require more methods than above should be split out into a separate controller.

**See more on structuring controllers:**

https://www.youtube.com/watch?v=MF0jFKvS4SI

#### Service Layer ####

* All business logic should be developed in classes
* Business logic in classes allow code to be reusable and thins out the Controllers
* Use the namespace App\Services
* Use the DRY concept when programming (Don't Repeat Yourself). Write code once and reuse it by calling that function.
* Functions should be as small, efficient and lean as possible
* If the function is getting large, break it up into smaller functions
* If reusing the same data (Question Types, Questions) use static to save additional database queries

#### Requests ####

* Use custom requests to handle data and validation
* Custom requests can be passed into the controller instead of the default request object
* Passing a custom request object means that the validation will be completed before reaching the controller.

**Read more on custom requests**
https://medium.com/@kamerk22/the-smart-way-to-handle-request-validation-in-laravel-5e8886279271

#### Jobs ####

* When a request is made that doesn't need to wait for a response (delete request, long loops), use a job.
* Jobs allow request to be returned faster and processes the time consuming part later in the job.
* Only do what is absolutely required to return a response from a request. The rest of the processing can be handled by jobs.
* Example 1: Adding data to the user log - Do this in a job so that the request response is faster
* Example 2: Uploading data - Do this in a job, loop the data as required. The response can say 'Data Processing' so that the user can continue with their task while the data happens in the job queue.

### Who do I talk to? ###

* Repo owner or admin:
* td@greenbizcheck.com
* Skype tim.dorey
* Ask in Slack (test deploy 002)
