Below is some useful information for making deployments that will help things go smoothly.
Feel free to ask questions or discuss on discord.


## How it Works
While not necessary, I think it will be useful to understand how CI works in case things _don't_ work
for some reason. There exists a github action in `.github/workflows` which outlines what the action does,
but it is just this:

1. Clone the repo to the `aptitude` server.
2. Install dependencies.
3. Open up file permissions.
4. Clean up our deployment location and move the new files there.


## How to Push Deployments
Deployments to `aptitude` are automatically triggered on every pull request, and whenever changes are
pushed to open pull requests. These deployments will overwrite the previously deployed files,
but since it's dev we don't really care.

1. Open a pull-request or push changes to pull request.
2. A task will be queued in the [actions tab](https://github.com/cse442-at-ub/sp25-project-o-no/actions/workflows/deploy.yml).
3. __If the aptitude runner is NOT already running__, you'll have to start it manually by running the script `run.sh` which I've placed on the server.
You can do this by running `/home/csdue/petervai/actions-runner/run.sh`while on the aptitude server.
4. Once the runner is running, it will automatically run any queued jobs.


## Tips and FAQ

* Aptitude Hostname: `aptitude.cse.buffalo.edu`
* Deployment Location (in aptitude): `/data/web/CSE442/2025-Spring/cse-442v`

Personally, I use the following alias to start up the runner:
```bash
alias cse442runner='ssh -t aptitude  "/data/web/CSE442/2025-Spring/cse-442v/actions-runner/run.sh"'
```
Note that `ssh aptitude` will not work on your computer, I use an alias in my ssh configuration.

