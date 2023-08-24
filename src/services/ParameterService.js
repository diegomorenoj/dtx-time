import axios from 'axios';
export default class ParameterService {
    getByType (type) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/type/${type}`).then(res => {
            return res.data;
        });
    }

    getStatus (trainingRequestId) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/status/${trainingRequestId}`).then(res => {
            return res.data;
        });
    }

    getSpecialties () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/specialties`).then(res => {
            return res.data;
        });
    }

    filterUsers (val) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/filterusers/${val}`).then(res => {
            return res.data;
        });
    }

    filterCities (val) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/filtercities/${val}`).then(res => {
            return res.data;
        });
    }

    filterAreas (val) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/filterareas/${val}`).then(res => {
            return res.data;
        });
    }

    filterPositions (val) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/filterpositions/${val}`).then(res => {
            return res.data;
        });
    }

    filterLevels (val) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/filterlevels/${val}`).then(res => {
            return res.data;
        });
    }

    filterAreasByCity (city, val) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/filterareasbycity/${city}/${val}`).then(res => {
            return res.data;
        });
    }

    getAllAreas () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/areas/all`).then(res => {
            return res.data;
        });
    }

    getPositionsByArea (area) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/positions/area/${area}`).then(res => {
            return res.data;
        });
    }

    getLevelsByPosition (position) {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/parameters/levels/position/${position}`).then(res => {
            return res.data;
        });
    }

    getCycles () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/cycles`).then(res => {
            return res.data;
        });
    }
}
