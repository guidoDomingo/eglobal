/*
 * FilePondPluginFileValidateSize 2.1.1
 * Licensed under MIT, https://opensource.org/licenses/MIT
 * Please visit https://pqina.nl/filepond for details.
 */

/* eslint-disable */
(function(global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined'
    ? (module.exports = factory())
    : typeof define === 'function' && define.amd
      ? define(factory)
      : (global.FilePondPluginFileValidateSize = factory());
})(this, function() {
  'use strict';

  var plugin$1 = function(_ref) {
    var addFilter = _ref.addFilter,
      utils = _ref.utils;

    // get quick reference to Type utils
    var Type = utils.Type,
      replaceInString = utils.replaceInString,
      toNaturalFileSize = utils.toNaturalFileSize;

    // filtering if an item is allowed in hopper

    addFilter('ALLOW_HOPPER_ITEM', function(file, _ref2) {
      var query = _ref2.query;

      if (!query('GET_ALLOW_FILE_SIZE_VALIDATION')) {
        return true;
      }

      var sizeMax = query('GET_MAX_FILE_SIZE');
      if (sizeMax !== null && file.size >= sizeMax) {
        return false;
      }

      var sizeMin = query('GET_MIN_FILE_SIZE');
      if (sizeMin !== null && file.size <= sizeMin) {
        return false;
      }

      return true;
    });

    // called for each file that is loaded
    // right before it is set to the item state
    // should return a promise
    addFilter('LOAD_FILE', function(file, _ref3) {
      var query = _ref3.query;
      return new Promise(function(resolve, reject) {
        // if not allowed, all fine, exit
        if (!query('GET_ALLOW_FILE_SIZE_VALIDATION')) {
          resolve(file);
          return;
        }

        // reject or resolve based on file size
        var sizeMax = query('GET_MAX_FILE_SIZE');
        if (sizeMax !== null && file.size >= sizeMax) {
          reject({
            status: {
              main: query('GET_LABEL_MAX_FILE_SIZE_EXCEEDED'),
              sub: replaceInString(query('GET_LABEL_MAX_FILE_SIZE'), {
                filesize: toNaturalFileSize(sizeMax)
              })
            }
          });
          return;
        }

        // reject or resolve based on file size
        var sizeMin = query('GET_MIN_FILE_SIZE');
        if (sizeMin !== null && file.size <= sizeMin) {
          reject({
            status: {
              main: query('GET_LABEL_MIN_FILE_SIZE_EXCEEDED'),
              sub: replaceInString(query('GET_LABEL_MIN_FILE_SIZE'), {
                filesize: toNaturalFileSize(sizeMin)
              })
            }
          });
          return;
        }

        // returns the current option value
        var totalSizeMax = query('GET_MAX_TOTAL_FILE_SIZE');
        if (totalSizeMax !== null) {
          // get the current total file size
          var currentTotalSize = query('GET_ACTIVE_ITEMS').reduce(function(
            total,
            item
          ) {
            return total + item.fileSize;
          },
          0);

          // get the size of the new file
          if (currentTotalSize > totalSizeMax) {
            reject({
              status: {
                main: query('GET_LABEL_MAX_TOTAL_FILE_SIZE_EXCEEDED'),
                sub: replaceInString(query('GET_LABEL_MAX_TOTAL_FILE_SIZE'), {
                  filesize: toNaturalFileSize(totalSizeMax)
                })
              }
            });
            return;
          }
        }

        // file is fine, let's pass it back
        resolve(file);
      });
    });

    return {
      options: {
        // Enable or disable file type validation
        allowFileSizeValidation: [true, Type.BOOLEAN],

        // Max individual file size in bytes
        maxFileSize: [null, Type.INT],

        // Min individual file size in bytes
        minFileSize: [null, Type.INT],

        // Max total file size in bytes
        maxTotalFileSize: [null, Type.INT],

        // error labels
        labelMinFileSizeExceeded: ['File is too small', Type.STRING],
        labelMinFileSize: ['Minimum file size is {filesize}', Type.STRING],

        labelMaxFileSizeExceeded: ['File is too large', Type.STRING],
        labelMaxFileSize: ['Maximum file size is {filesize}', Type.STRING],

        labelMaxTotalFileSizeExceeded: [
          'Maximum total size exceeded',
          Type.STRING
        ],
        labelMaxTotalFileSize: [
          'Maximum total file size is {filesize}',
          Type.STRING
        ]
      }
    };
  };

  var isBrowser =
    typeof window !== 'undefined' && typeof window.document !== 'undefined';

  if (isBrowser) {
    document.dispatchEvent(
      new CustomEvent('FilePond:pluginloaded', { detail: plugin$1 })
    );
  }

  return plugin$1;
});
