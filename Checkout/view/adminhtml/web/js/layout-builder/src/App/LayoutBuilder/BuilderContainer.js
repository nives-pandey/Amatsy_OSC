import { connect } from 'react-redux';
import BuilderComponent from './BuilderComponent';
import { ActionCreators } from './store/actions';

const mapStateToProps = (state) => ({
    layoutBuilderConfig: state.layoutBuilder.layoutBuilderConfig
});

const mapDispatchToProps = {
    changeBuilderConfigAction: ActionCreators.changeBuilderConfigAction,
    changeBuilderLayoutAction: ActionCreators.changeBuilderLayoutAction,
    changeBuilderWidthAction: ActionCreators.changeBuilderWidthAction
};

const BuilderContainer = connect(
    mapStateToProps,
    mapDispatchToProps
)(BuilderComponent);

export default BuilderContainer;
