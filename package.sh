echo Removing unnecessary files from distribution
rm -rf _db
rm -rf _tests
rm -rf _transifex
rm -f Gruntfile.js
rm -f package.json
rm -f readme.md
rm -f readme.template.html
rm -f .bower.json
rm -f .gitmodules
echo Removing myself now
rm -f package.sh
echo Stepping out
cd ..
echo Compressing...
currentbasename=${PWD##*/} 	# Assign current basename to variable
zip -qr9 ${currentbasename}.zip b2evolution 
