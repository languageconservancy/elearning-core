export declare type TransitionFunction = (data?: any) => void;

export declare type Transition = {
    [key: string]: TransitionFunction;
};

export declare type State = {
    [key: string]: Transition;
};

export declare type StateMachine = {
    state: string;
    transitions: State;
    dispatch: (actionName: string, data?: any) => void;
    transition: (nextState: string, data?: any) => void;
};
