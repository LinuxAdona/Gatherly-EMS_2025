# How to Setup GEMS

Make sure that you have installed the latest version of [Node.js](https://nodejs.org/en/download).

1. Go to the folder where you want to place the project and Open CMD or Git Bash:

```
git clone https://github.com/LinuxAdona/
Gatherly-EMS_2025.git
git fetch
```

2. After cloning the repository, go ahead and enter the directory and open VSCode with:

```
cd Gatherly-EMS_2025
code .
```

3. In VSCode, go ahead and open a terminal from the top: Terminal > New Terminal and run:

```
git fetch
```

4. Now run this command:

```
Set-ExecutionPolicy -Scope LocalMachine -ExecutionPolicy RemoteSigned
npm install
npm run dev
```

5. You should be able to see this from the terminal:

```
> gems@0.0.0 dev
> vite

  VITE v7.1.11  ready in #### ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  ➜  press h + enter to show help
```

# How to setup Github

1. Open VSCode > Extensions and search for GitLens

2. A new icon will appear on the left sidebar and in there, you can add your new files and commit them. (Make sure you include a comment to the commit or else it won't accept it.)

If there are issues in the process, please message [Linux Adona](https://www.facebook.com/Linux.Sale.Adona).
