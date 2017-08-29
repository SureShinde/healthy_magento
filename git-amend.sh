# Changing author info in the Git history of the repository
# Before run this scripe, replace the following variables with new info
#  - OLD_EMAIL		the old email to be replaced
#  - CORRECT_NAME	the new author's name
#  - CORRECT_EMAIL	the new author's email

#!/bin/sh

git filter-branch --env-filter '
OLD_EMAIL="truglio1987@hotmail.com"
CORRECT_NAME="dmitrymelnik2017"
CORRECT_EMAIL="dmitry.melnik-2017@yandex.com"
if [ "$GIT_COMMITTER_EMAIL" = "$OLD_EMAIL" ]
then
    export GIT_COMMITTER_NAME="$CORRECT_NAME"
    export GIT_COMMITTER_EMAIL="$CORRECT_EMAIL"
fi
if [ "$GIT_AUTHOR_EMAIL" = "$OLD_EMAIL" ]
then
    export GIT_AUTHOR_NAME="$CORRECT_NAME"
    export GIT_AUTHOR_EMAIL="$CORRECT_EMAIL"
fi
' --tag-name-filter cat -- --branches --tags
