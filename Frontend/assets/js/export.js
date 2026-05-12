/**
 * Export Module
 * weather-system/frontend/assets/js/export.js
 */

const Export = (() => {

  const historyCSV = () => {
    API.download('/export/history/csv');
  };

  const favoritesCSV = () => {
    API.download('/export/favorites/csv');
  };

  const historyPDF = () => {
    API.download('/export/history/pdf');
  };

  return { historyCSV, favoritesCSV, historyPDF };
})();