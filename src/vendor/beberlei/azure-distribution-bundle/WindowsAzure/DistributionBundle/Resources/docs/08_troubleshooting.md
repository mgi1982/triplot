---
layout: default
title: Troubleshooting
---

# Troubleshooting

1. Large cspkg files

    Currently the vendor directories are included in cspkg, which can lead to large files to be generated. This obviously causes network traffic and initial startup time overhead. The plan is to change this in the near future by using composer to fetch all the necessary dependencies on the web and worker roles during deployment.

2. Path is too long during cspack.exe or csrun.exe

    Some Symfony2 bundles contain deep paths and hence your app does too. Windows filesystem functions have a limit of 248/260 chars for paths/files. This can lead to problem when packaging Azure applications, because the files are copied to a temporary directory inside the user directory during this operation. This bundle includes a detection for very long file names and throws an exception detailing the file paths that can cause problems.

    Another solution when this occurs during execution of "csrun.exe" is to set the environment variable `_CSRUN_STATE_DIRECTORY=c:\t` to a small directory, in this case `c:\t`.

3. Access denied during cspack.exe

    You have to make sure that the specified output-path is writable and that the files/directories in there can be cleaned up during the package command.
