module.exports = config => {
    config.multipart = {
        mode: 'file',
        // fileSize: "50mb",
        // mode: "stream",
        fileExtensions: [
            ".jpg",
            ".JPG",
            ".png",
            ".PNG",
            ".gif",
            ".GIF",
            ".jpeg",
            ".JPEG",
        ],
    };
}
