cd /chroot/home/magentot/magento2.qdos-technology.com/html/pub/media/import/
# change ..JPG files to .jpg
for file in *.JPG ; do mv $file `echo $file | sed 's/\(.*\.\)JPG/\1jpg/'` ; done