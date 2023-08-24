import axios from 'axios';
export default class CourseService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/courses`).then(res => {
            return res.data;
        });
    }

    getAllByFilter (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/courses/filter`, filter).then(res => {
            return res.data;
        });
    }

    getDashboardByFilter (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/courses/dashboard/filter`, filter).then(res => {
            return res.data;
        });
    }

    getInstructorsByFilter (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/courses/instructors/filter`, filter).then(res => {
            return res.data;
        });
    }

    create (data) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/courses`, data).then(res => {
            return res.data;
        });
    }

    importCourses (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/courses/excel/course`, filter).then(res => {
            return res.data;
        });
    }

    importUsers (filter) {
        return axios.post(`${process.env.VUE_APP_RUTA_API}/courses/excel/user`, filter).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/courses/${id}`, data).then(res => {
            return res.data;
        });
    }

    delete (id) {
        return axios.delete(`${process.env.VUE_APP_RUTA_API}/courses/${id}`).then(res => {
            return res.data;
        });
    }

    changeStatus (id, statusId) {
        const data = { status_id: statusId };
        return axios.put(`${process.env.VUE_APP_RUTA_API}/courses/changestatus/${id}`, data)
        .then(res => {
            console.log('CHANGE STATUS', res.data);
            return res.data;
        });
    }
}
