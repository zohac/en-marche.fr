import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import MovementIdeas from '../components/MovementIdeas';
import LatestIdeas from '../containers/LatestIdeas';
import Reports from '../containers/Reports';
import { initHomePage } from '../redux/thunk/navigation';

class Home extends React.Component {
    componentDidMount() {
        this.props.initHomePage();
    }

    render() {
        return (
            <div className="home-page">
                <MovementIdeas />
                <LatestIdeas />
                <Reports />
            </div>
        );
    }
}

Home.propTypes = {
    initHomePage: PropTypes.func.isRequired,
};

export default connect(
    null,
    {
        initHomePage,
    }
)(Home);
